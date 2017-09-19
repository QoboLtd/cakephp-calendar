<?php
namespace Qobo\Calendar\Shell\Task;

use Cake\Console\Shell;
use Cake\ORM\TableRegistry;
use Qobo\Utils\Utility\FileLock;

/**
 * Sync shell task.
 */
class SyncTask extends Shell
{

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

        $calendarsProcessed = 1;
        $output = [];

        $this->info('Preparing for calendar sync...');

        $result = $options = [];
        $table = TableRegistry::get('Qobo/Calendar.Calendars');

        if (!empty($this->params['start'])) {
            $options['period']['start_date'] = $this->params['start'];
        }

        if (!empty($this->params['end'])) {
            $options['period']['end_date'] = $this->params['end'];
        }

        $status = $table->sync($options);

        dd('w00t');

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
     * syncAttendees method
     *
     * Synchronizing attendees (users) for calendar events auto-complete
     *
     * @return void
     */
    protected function syncAttendees()
    {
        //sync all the attendees from users.
        $usersTable = TableRegistry::get('Users');
        $users = $usersTable->find()->all();
        $attendeesTable = TableRegistry::get('Qobo/Calendar.CalendarAttendees');
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

            $existing = $attendeesTable->exists(['contact_details' => $user->email]);

            if (!$existing) {
                $entity = $attendeesTable->newEntity();

                $entity->display_name = $user->name;
                $entity->contact_details = $user->email;

                $saved = $attendeesTable->save($entity);
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
     * @param Table $table of calendar instance.
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
        $usersTable = TableRegistry::get('Users');
        $users = $usersTable->find()->all();

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
