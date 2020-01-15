<?php
/**
 * Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Qobo Ltd. (https://www.qobo.biz)
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace Qobo\Calendar\Shell\Task;

use CakeDC\Users\Controller\Traits\CustomUsersTableTrait;
use Cake\Console\Shell;
use Cake\ORM\TableRegistry;
use Exception;
use Qobo\Utils\Utility\FileLock;

/**
 * Sync shell task.
 */
class SyncTask extends Shell
{
    use CustomUsersTableTrait;

    protected $usersTable;

    protected $attendeesTable;

    protected $calendarsTable;

    /**
     * Manage available options via Parser
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->setDescription(
            __('Synchronize local and remote calendars with the database')
        );

        $parser->addOption('start', [
            'description' => __('Specify start interval for the events to fetch'),
            'help' => __("Start date 'YYYY-MM-DD HH:MM:SS' for events to fetch"),
        ]);

        $parser->addOption('end', [
            'description' => __('Specify end interval for the events to fetch'),
            'help' => __("End date 'YYYY-MM-DD HH:MM:SS' for events to fetch"),
        ]);

        return $parser;
    }

    /**
     * main() method.
     *
     * @return bool|int|null Success or error code.
     */
    public function main()
    {
        try {
            $lock = new FileLock('import_' . md5(__FILE__) . '.lock');
        } catch (Exception $e) {
            $this->abort($e->getMessage());
        }

        if (!$lock->lock()) {
            $this->abort('Import is already in progress');
        }

        $this->attendeesTable = TableRegistry::get('Qobo/Calendar.CalendarAttendees');
        $this->calendarsTable = TableRegistry::get('Qobo/Calendar.Calendars');
        $this->usersTable = $this->getUsersTable();

        $calendarsProcessed = 1;
        $output = $result = $options = [];

        $progress = $this->helper('Progress');
        $progress->init();

        $this->info('Preparing for calendar sync...');

        $options = $this->setDefaultTimePeriod($this->params);

        $result['calendars'] = $this->calendarsTable->syncCalendars($options);

        if (empty($result['calendars'])) {
            $this->abort('No calendars found for synchronization');
        }

        foreach ($result['calendars'] as $actionName => $calendars) {
            foreach ($calendars as $k => $calendar) {
                $resultEvents = $this->calendarsTable->syncCalendarEvents($calendar, $options);

                $resultAttendees = $this->calendarsTable->syncEventsAttendees($calendar, $resultEvents);

                $output[] = [
                    'action' => $actionName,
                    'calendar' => $calendar,
                    'events' => $resultEvents,
                    'attendees' => $resultAttendees,
                ];

                $progress->increment(100 / ++$calendarsProcessed);
                $progress->draw();
            }
        }

        $this->syncAttendees();
        $birthdays = $this->syncBirthdays($this->calendarsTable);

        $this->out(null);
        $this->success('Synchronization complete!');
        $this->out(null);

        if (true == $this->params['verbose']) {
            print_r($output);
        }

        $lock->unlock();

        return $output;
    }

    /**
     * Set Default Time period
     *
     * @param array $params with CLI period options
     * @return array $options with prepopulated opts.
     */
    protected function setDefaultTimePeriod(array $params = [])
    {
        $options = [];
        if (!empty($params['start'])) {
            $options['period']['start_date'] = $params['start'];
        }

        if (!empty($params['end'])) {
            $options['period']['end_date'] = $params['end'];
        }

        return $options;
    }

    /**
     * syncAttendees method
     *
     * Synchronizing attendees (users) for calendar events auto-complete
     *
     * @return void
     */
    protected function syncAttendees()
    {
        //sync all the attendees from users.
        $users = $this->usersTable->find()->all();
        $result = [];

        $progress = $this->helper('Progress');
        $progress->init();
        $this->out(null);
        $this->info('Syncing attendees...');

        $count = 1;
        foreach ($users as $k => $user) {
            if (empty($user->email)) {
                continue;
            }

            $existing = $this->attendeesTable->exists(['contact_details' => $user->email]);

            if (!$existing) {
                $entity = $this->attendeesTable->newEntity();

                $entity->display_name = $user->name;
                $entity->contact_details = $user->email;

                $saved = $this->attendeesTable->save($entity);
                if ($saved) {
                    array_push($result, $saved);
                }
            }

            $progress->increment(100 / ++$count);
            $progress->draw();
        }

        $this->out(null);

        if (!empty($result)) {
            $this->out('<success> [' . count($result) . ']Attendees synchronized!</success>');
        }

        $this->out(null);
    }

    /**
     * syncBirthdays method
     *
     * Create basic birthdays calendar with
     * yearly recurring events
     *
     * @param \Cake\ORM\Table $table of calendar instance.
     *
     * @return array $result containing users/events saved/updated.
     */
    protected function syncBirthdays($table = null)
    {
        $result = [
            'error' => [],
            'added' => [],
            'updated' => [],
        ];

        $eventsTable = TableRegistry::get('Qobo/Calendar.CalendarEvents');

        $users = $this->usersTable->find()->all();

        $progress = $this->helper('Progress');
        $progress->init();
        $this->info('Syncing birthday calendar...');

        $calendar = $table->find()
            ->where([
                'source' => 'Plugin__',
                'name' => 'Birthdays',
            ])->first();

        if (empty($calendar)) {
            $entity = $table->newEntity();
            $entity->name = 'Birthdays';
            $entity->source = 'Plugin__';
            $entity->icon = 'birthday-cake';

            $calendar = $table->save($entity);
        }

        $count = 1;
        foreach ($users as $k => $user) {
            if (empty($user->birthdate)) {
                $result['error'][] = "User ID: {$user->id} doesn't have birth date in the system";
                continue;
            }

            $birthdayEvent = $eventsTable->find()
                ->where([
                    'calendar_id' => $calendar->id,
                    'content LIKE' => "%{$user->first_name} {$user->last_name}%",
                    'is_recurring' => 1,
                ])->first();

            if (!$birthdayEvent) {
                $entity = $eventsTable->newEntity();
                $entity->calendar_id = $calendar->id;
                $entity->title = sprintf("%s %s", $user->first_name, $user->last_name);
                $entity->content = sprintf("%s %s", $user->first_name, $user->last_name);
                $entity->is_recurring = true;
                $entity->is_allday = true;

                $entity->start_date = date('Y-m-d 09:00:00', strtotime($user->birthdate));
                $entity->end_date = date('Y-m-d 18:00:00', strtotime($user->birthdate));
                $entity->recurrence = json_encode(['RRULE:FREQ=YEARLY']);
                $birthdayEvent = $eventsTable->save($entity);

                $result['added'][] = $birthdayEvent;
            } else {
                $entity = $eventsTable->patchEntity($birthdayEvent, [
                    'title' => sprintf("%s %s", $user->first_name, $user->last_name),
                    'is_allday' => true,
                ]);

                $birthdayEvent = $eventsTable->save($entity);
                $result['updated'][] = $birthdayEvent;
            }

            $progress->increment(100 / ++$count);
            $progress->draw();
        }

        $this->out(null);
        $this->out('<success> Added [' . count($result['added']) . '], Updated [' . count($result['updated']) . '] events!</success>');
        $this->out(null);

        return $result;
    }
}
