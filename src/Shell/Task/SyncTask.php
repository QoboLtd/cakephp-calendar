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
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Exception;
use Qobo\Utils\Utility\Lock\FileLock;

/**
 * Sync shell task.
 */
class SyncTask extends Shell
{
    use CustomUsersTableTrait;

    /** @var \CakeDC\Users\Model\Table\UsersTable */
    protected $usersTable;

    /** @var \Qobo\Calendar\Model\Table\CalendarAttendeesTable */
    protected $attendeesTable;

    /** @var \Qobo\Calendar\Model\Table\CalendarsTable */
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
            (string)__('Synchronize local and remote calendars with the database')
        );

        $parser->addOption('start', [
            'description' => (string)__('Specify start interval for the events to fetch'),
            'help' => (string)__("Start date 'YYYY-MM-DD HH:MM:SS' for events to fetch"),
        ]);

        $parser->addOption('end', [
            'description' => (string)__('Specify end interval for the events to fetch'),
            'help' => (string)__("End date 'YYYY-MM-DD HH:MM:SS' for events to fetch"),
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

            // Rethrow the exception if some reason we have reached this point
            // This shouldn't happen since `$this->abort` throw another Exception
            throw $e;
        }

        if (!$lock->lock()) {
            $this->abort('Import is already in progress');
        }

        /** @var \Qobo\Calendar\Model\Table\CalendarAttendeesTable $table */
        $table = TableRegistry::get('Qobo/Calendar.CalendarAttendees');
        $this->attendeesTable = $table;

        /** @var \Qobo\Calendar\Model\Table\CalendarsTable $table */
        $table = TableRegistry::get('Qobo/Calendar.Calendars');
        $this->calendarsTable = $table;

        /** @var \CakeDC\Users\Model\Table\UsersTable $table */
        $table = $this->getUsersTable();
        $this->usersTable = $table;

        $calendarsProcessed = 1;
        $output = $result = $options = [];

        /** @var \Cake\Shell\Helper\ProgressHelper $progress */
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
    }

    /**
     * Set Default Time period
     *
     * @param mixed[] $params with CLI period options
     * @return mixed[] $options with prepopulated opts.
     */
    protected function setDefaultTimePeriod(array $params = []): array
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
    protected function syncAttendees(): void
    {
        //sync all the attendees from users.
        $users = $this->usersTable->find()->all();
        $result = [];

        /** @var \Cake\Shell\Helper\ProgressHelper $progress */
        $progress = $this->helper('Progress');
        $progress->init();
        $this->out(null);
        $this->info('Syncing attendees...');

        $count = 1;
        /** @var \Cake\Datasource\EntityInterface $user */
        foreach ($users as $k => $user) {
            if (empty($user->email)) {
                continue;
            }

            $existing = $this->attendeesTable->exists(['contact_details' => $user->email]);

            if (!$existing) {
                $entity = $this->attendeesTable->newEntity();

                $entity->set('display_name', $user->get('name'));
                $entity->set('contact_details', $user->get('email'));

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
     * @return mixed[] $result containing users/events saved/updated.
     */
    protected function syncBirthdays(Table $table): array
    {
        $result = [
            'error' => [],
            'added' => [],
            'updated' => [],
        ];

        /** @var \Qobo\Calendar\Model\Table\CalendarsTable $eventsTable */
        $eventsTable = TableRegistry::get('Qobo/Calendar.CalendarEvents');
        $users = $this->usersTable->find()->all();

        /** @var \Cake\Shell\Helper\ProgressHelper $progress */
        $progress = $this->helper('Progress');
        $progress->init();
        $this->info('Syncing birthday calendar...');

        /** @var \Cake\Datasource\EntityInterface $calendar */
        $calendar = $table->find()
            ->where([
                'source' => 'Plugin__',
                'name' => 'Birthdays',
            ])->first();

        if (empty($calendar)) {
            $entity = $table->newEntity();
            $entity->set('name', 'Birthdays');
            $entity->set('source', 'Plugin__');
            $entity->set('icon', 'birthday-cake');

            $calendar = $table->saveOrFail($entity);
        }

        $count = 1;
        /** @var \Cake\Datasource\EntityInterface $user */
        foreach ($users as $k => $user) {
            if (empty($user->get('birthdate'))) {
                $result['error'][] = "User ID: {$user->get('id')} doesn't have birth date in the system";
                continue;
            }

            /** @var \Cake\Datasource\EntityInterface $birthdayEvent */
            $birthdayEvent = $eventsTable->find()
                ->where([
                    'calendar_id' => $calendar->get('id'),
                    'content LIKE' => "%{$user->get('first_name')} {$user->get('last_name')}%",
                    'is_recurring' => 1,
                ])->first();

            if (!empty($birthdayEvent)) {
                $entity = $eventsTable->newEntity();
                $entity->set('calendar_id', $calendar->get('id'));
                $entity->set('title', sprintf("%s %s", $user->get('first_name'), $user->get('last_name')));
                $entity->set('content', sprintf("%s %s", $user->get('first_name'), $user->get('last_name')));
                $entity->set('is_recurring', true);
                $entity->set('is_allday', true);

                $entity->set('start_date', date('Y-m-d 09:00:00', strtotime($user->get('birthdate'))));
                $entity->set('end_date', date('Y-m-d 18:00:00', strtotime($user->get('birthdate'))));
                $entity->set('recurrence', json_encode(['RRULE:FREQ=YEARLY']));
                $birthdayEvent = $eventsTable->save($entity);

                $result['added'][] = $birthdayEvent;
            } else {
                $entity = $eventsTable->patchEntity($birthdayEvent, [
                    'title' => sprintf("%s %s", $user->get('first_name'), $user->get('last_name')),
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
