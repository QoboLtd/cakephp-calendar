// @codingStandardsIgnoreStart
// loading v-select plugin for Select2 functionality.
Vue.component('v-select', VueSelect.VueSelect);

Vue.component('icon-component', {
    template: `<i class="fa" v-bind:class="getIcon(name)">&nbsp;</i>`,
    props: ['name'],
    methods: {
        getIcon(name) {
            return (name) ? 'fa-' + name : '';
        }
    }
});
Vue.component('calendar-link', {
    template: `<a :href="getUrl(itemUrl, itemValue)" :class="itemClass">
                    <icon-component v-if="itemIcon" :name="itemIcon"></icon-component>
                </a>`,
    props: ['itemIcon', 'itemValue', 'itemUrl', 'itemClass', 'itemDelete', 'itemConfirmMsg'],
    methods: {
        getUrl(url, id) {
            return (id) ? url + '/' + id : url;
        }
    }
});

Vue.component('calendar-item', {
    template: `<div class="form-group checkbox">
                <label>
                    <input type="checkbox" @click="toggleCalendar(value)" v-model="toggle" :value="value" :name="name" :multiple="{ multiple: itemIsMultiple }" :class="[itemClass]"/>
                        <icon-component v-if="icon" :name="icon"></icon-component>
                        {{label}}
                </label>
                </div>`,
    props: ['label', 'value', 'icon','itemActive', 'name'],
    components: ['icon-component'],
    beforeMount: function() {
        this.toggle = this.itemActive;
    },
    data() {
        return {
            toggle: null,
            itemClass: 'calendar-id',
            itemIsMultiple: true,
        };
    },
    methods: {
        toggleCalendar(calendarId) {
            this.$emit('toggle-calendar', this.toggle, calendarId );
        }
    }
});

Vue.component('calendar', {
    template: '<div></div>',
    props: ['ids', 'events', 'editable', 'start', 'end', 'timezone', 'public', 'showPrintButton'],
    data() {
        return {
            calendarInstance: null,
            calendarEvents: [],
            format: 'YYYY-MM-DD',
        };
    },
    watch: {
        events: function() {
            var self = this;
            // @FIXME: remove only the ones that deserve it.
            this.calendarEvents = [];
            if (!this.events.length) {
                return;
            }

            this.events.forEach( (event, index) => {
                self.calendarEvents.push({
                    id: event.id,
                    title: event.title,
                    color: event.color,
                    start: moment().format(event.start_date),
                    end: moment().format(event.end_date),
                    calendar_id: event.calendar_id,
                    allDay: event.is_allday,
                    event_type: event.event_type
                });
            });
        },
        calendarEvents: function() {
            this.calendarInstance.fullCalendar('removeEvents');
            this.calendarInstance.fullCalendar('addEventSource', this.calendarEvents);
        }
    },
    mounted() {
        var self = this;
        self.calendarInstance = $(self.$el);

        var args = {
            header: {
                left: 'today, prev,next printButton',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            buttonText: {
                today: 'today',
                month: 'month',
                week: 'week',
                day: 'day'
            },
            firstDay: 1,
            defaultDate: moment(this.start),
            editable: this.editable,
            eventClick(event) {
                self.$emit('event-info', event);
            },
            viewRender(view, element) {
                self.$emit('interval-update', view.start.format(this.format), view.end.format(this.format));
            }
        };

        if (true === this.showPrintButton) {
            args.customButtons = {
                printButton: {
                    text: 'Print',
                    click: function() {
                        window.print();
                    }
                }
            };
        }

        //@TODO: editable/public/active flags should
        // be sorted out.
        if (this.public != 'true') {
            args.dayClick = function(date, jsEvent, view) {
                self.$emit('modal-add-event', date, jsEvent, view);
            };
        }

        this.calendarInstance.fullCalendar(args);
    },
});

Vue.component('input-select', {
    template: `<div class="form-group">
        <label>{{label}}</label>
        <select v-model="value" class="form-control" :name="name">
            <option v-for="item in options" :value="item.value">{{item.label}}</option>
        </select>
    </div>`,
    props: ['options', 'name', 'label'],
    data() {
        return {
            value: null,
        };
    },
    watch: {
        value: function() {
            this.$emit('changed', this.value);
        }
    }
});

Vue.component('input-checkboxes', {
    template: `
         <div class="form-group">
            <label v-for="item in options">
                <span style="padding:5px;"> <input type="checkbox" v-model="values" :value="item.value"/> {{item.label}}</span>
            </label>
        </div>`,
    data() {
        return {
            options: [
                {label: 'Monday', value: 'MO'},
                {label: 'Tuesday', value: 'TU'},
                {label: 'Wednesday', value: 'WE'},
                {label: 'Thursday', value: 'TH'},
                {label: 'Friday', value: 'FR'},
                {label: 'Saturday', value: 'SA'},
                {label: 'Sunday', value: 'SU'}
            ],
            values: [],
        };
    },
    watch: {
        values: function() {
            this.$emit('changed', this.values);
        },
    }
});

Vue.component('calendar-recurring-until', {
    template:
    `<div class="form-group">
        <span><strong>Ends:</strong></span>
        <div class='form-group radio'>
            <label><input type="radio" v-model="rtype" value="infinity"/>Never</label>
        </div>
        <div class='form-group radio'>
            <label>
                <input type="radio" v-model="rtype" value="occurrence"/>
                After # of occurrences:
                <div class="form-group text">
                    <input class="form-control" type="text" v-model="valueOcc" :disabled="rtype !== 'occurrence'"/>
                </div>
            </label>
        </div>
        <div class='form-group radio'>
            <label>
                <input type="radio" v-model="rtype" value="date">
                Until Date (not including):
                <input-datepicker
                        name="CalendarEvents[until]"
                        :disabled="rtype !== 'date'"
                        class-name="calendar-until-datetimepicker"
                        format="YYYY-MM-DD"
                        isUp="true"
                        @date-changed="setUntilDate">
                    </input-datepicker>
            </label>
        </div>
    </div>`,
    data: function() {
        return {
            rtype: null,
            valueDate: null,
            valueOcc: null,
        };
    },
    computed: {
        isUntilChanged: function() {
            return [this.rtype,this.valueOcc, this.valueDate].join('');
        },
        isTypeChanged: function() {
            return this.rtype;
        },
    },
    watch: {
        isUntilChanged: function() {
            var value = null;
            if (this.rtype == 'date') {
                value = this.valueDate;
            }

            if (this.rtype == 'occurrence') {
                value = this.valueOcc;
            }

            this.$emit('data-changed', this.rtype, value);
        },
        isTypeChanged: function() {
            this.valueOcc = null;
        },
    },
    methods: {
        setUntilDate(val) {
            this.valueDate = val;
        },
    },
});

Vue.component('input-datepicker-range', {
    template: `<div><div class="col-xs-12 col-md-6">
        <input-datepicker
            v-bind:date-moment="startMoment"
            :name="startName"
            :label="startLabel"
            :is-start="true"
            :class-name="startClass"
            format="YYYY-MM-DD HH:mm"
            @date-changed="setStartDate">
        </input-datepicker>
    </div>
    <div class="col-xs-12 col-md-6">
        <input-datepicker
            :date-moment="endMoment"
            :name="endName"
            :is-start="false"
            :label="endLabel"
            :class-name="endClass"
            format="YYYY-MM-DD HH:mm"
            @date-changed="setEndDate">
        </input-datepicker>
    </div></div>`,
    props: ['startName', 'endName', 'startLabel', 'endLabel', 'startClass', 'endClass', 'configs', 'eventClick'],
    data: function() {
        return {
            startDate: null,
            startMoment: null,
            endDate: null,
            endMoment: null,
            format: 'YYYY-MM-DD HH:mm',
        };
    },
    computed: {
        /* using compute var to avoid multiple event calls for start/end changes. */
        intervalChanged: function() {
            return [this.startDate, this.endDate].join('');
        },
    },
    watch: {
        configs: function() {
            var st = [];
            var en = [];
            if (!this.configs) {
                return;
            }
            if (this.configs.hasOwnProperty('start_time')) {
                st = this.configs.start_time.split(':');
                var newStart = moment(this.startMoment).set({'hour': st[0], 'minute' : st[1]});

                this.setStartDate(newStart.format(this.format), newStart);
            }

            if (this.configs.hasOwnProperty('end_time')) {
                en = this.configs.end_time.split(':');
                var newEnd = moment(this.endMoment).set({'hour': en[0], 'minute' : en[1]});

                this.setEndDate(newEnd.format(this.format), newEnd);
            }
        },
        eventClick: function() {
            this.startMoment = moment(this.eventClick);
            this.endMoment = moment(this.eventClick);

            this.startDate = this.startMoment.format(this.format);
            this.endDate = this.endMoment.format(this.format);
        },
        intervalChanged: function() {
            this.$emit('date-updated', this.startDate, this.endDate);
        }
    },
    methods: {
        setStartDate: function(val, momentObj) {
            this.startDate = val;
            this.startMoment = moment(momentObj);
        },
        setEndDate: function(val, momentObj) {
            this.endDate = val;
            this.endMoment = moment(momentObj);
        },
    }
});

Vue.component('input-datepicker', {
    template: `
        <div class="form-group text">
            <label v-if="label">{{label}}</label>
            <input type="text" :disabled="disabled" :name="name" :value="value" :class="className" class="form-control"/>
        </div>`,
    props: ['name', 'className', 'label', 'disabled', 'isStart', 'dateMoment','format', 'isUp'],
    beforeMount: function() {
        if (this.format) {
            this.pickerOptions.format = this.format;
        } else {
            this.pickerOptions.format = 'YYYY-MM-DD HH:mm';
        }

        if (this.isUp) {
            this.pickerOptions.drops = 'up';
        }
    },
    mounted: function() {
        var self = this;
        self.instance = $(self.$el).find('input').daterangepicker(this.pickerOptions).data('daterangepicker');

        $(self.$el).find('input').on('apply.daterangepicker', function(ev, picker) {
            self.momentObject = moment(picker.startDate);
            self.value = picker.startDate.format(self.pickerOptions.format);
            self.$emit('date-changed', self.value, self.momentObject)
        });
    },
    watch: {
        dateMoment: function() {
            this.momentObject = moment(this.dateMoment);
            this.instance.setStartDate(this.momentObject);
            this.instance.setEndDate(this.momentObject);
            this.value = this.momentObject.format(this.pickerOptions.format);
        },
    },
    data: function() {
        return {
            instance: null,
            value: null,
            momentObject: null,
            pickerOptions: {
                singleDatePicker: true,
                showDropdowns: true,
                timePicker: true,
                drops: "down",
                timePicker12Hour: false,
                timePickerIncrement: 5,
            },
        };
    },
});


Vue.component('calendar-modal', {
    template: `<div class="modal fade" id="calendar-modal-add-event" tabindex="-1" role="dialog" aria-labelledby="calendar-modal-label">
            <div class="modal-dialog" role="document">
                <div class="modal-content" v-if="calendarsList.length == 0">
                     <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="calendar-modal-label">Warning</h4>
                    </div>
                    <div class="modal-body">
                        <p>You don't have permissions to add event to calendars.</p>
                    </div>
                    <div class="modal-footer">
                        <button v-on:click="dismissModal" class="btn btn-default">Close</button>
                    </div>
                </div>
                <div class="modal-content" v-else>
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="calendar-modal-label">Add Event</h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-xs-12 col-md-12">
                                <div class="form-group">
                                    <v-select v-model="calendarId" :options="calendarsList" placeholder="-- Please choose Calendar --"></v-select>
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-12">
                                <div class="form-group">
                                    <v-select v-model="eventType" :options="eventTypesList" placeholder="-- Please choose Event Type --"></v-select>
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-12">
                                <div class="row">
                                    <input-datepicker-range
                                        start-name="CalendarEvents[start_date]"
                                        end-name="CalendarEvents[end_date]"
                                        start-label="Start Date:"
                                        end-label="End Date:"
                                        :configs="eventTypeConfig"
                                        start-class="calendar-start-datetimepicker"
                                        end-class="calendar-end-datetimepicker"
                                        :event-click="eventClick"
                                        @date-updated="setDateRange">
                                    </input-datepicker-range>

                                    <div class="col-xs-12 col-md-12">
                                        <div class="form-group text">
                                            <label> Attendees: </label>
                                            <v-select
                                                v-model="attendeesList"
                                                :debounce="400"
                                                :on-search="searchAttendees"
                                                :options="attendees"
                                                multiple>
                                            </v-select>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-md-12" v-if="!isRecurring">
                                        <div class="form-group text">
                                        <label>Title:</label>
                                        <input type="text" v-model="title" class="form-control"/>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-md-12" v-if="!isRecurring">
                                        <div class="form-group text">
                                            <label>Content:</label>
                                            <textarea v-model="content" class="form-control"></textarea>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-md-12">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="form-group text">
                                                    <label>Repeats:</label>
                                                    <input type="checkbox" name="CalendarEvents[is_recurring]" v-model="isRecurring"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xs-12 col-md-12" v-if="isRecurring">
                                        <input-select
                                            name="CalendarEvents[frequency]"
                                            :options="frequencies"
                                            label="Frequency:"
                                            @changed="getFrequency">
                                        </input-select>
                                    </div>

                                    <div class="col-xs-12 col-md-12" v-if="isWeekly || isYearly || isDaily || isMonthly">
                                        <input-select
                                            name="CalendarEvents[intervals]"
                                            :options="frequencyIntervals"
                                            label="Interval:"
                                            @changed="getInterval">
                                        </input-select>
                                    </div>

                                    <div class="col-xs-12 col-md-12" v-if="isWeekly">
                                        <input-checkboxes @changed="getWeekDays"></input-checkboxes>
                                    </div>

                                    <div class="col-xs-12 col-md-12" v-if="isRecurring">
                                        <calendar-recurring-until @data-changed="getUntil"></calendar-recurring-until>
                                    </div>

                                    <div class="col-xs-12 col-md-12" v-if="isRecurring">
                                        Recurring Event: {{rruleResult}}
                                    </div>
                                </div> <!-- .row -->
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button v-on:click="submitEvent" class="btn btn-default">Save</button>
                        <button v-on:click="dismissModal" class="btn btn-default">Close</button>
                    </div>
                </div>
            </div>
        </div>
        `,
    props: ['calendarsList', 'timezone', 'eventClick'],
    data: function() {
        return {
			calendarId: null,
            startDate: null,
            endDate:null,
            title: null,
            content: null,
            attendees: [],
			attendeesList: [],
            eventType: null,
            eventTypeConfig: null,
            eventTypes: [],
			eventTypesList: [],

            isRecurring: 0,
            frequency: null,
            interval: null,
            frequencyIntervals: [],
            frequencies: [
                { value: 3, label: 'Daily', },
                { value: 2, label: 'Weekly' },
                { value: 1, label: 'Monthly' },
                { value: 0, label: 'Yearly' },
            ],
            weekDays: [],
            untilOption: null,
            untilValue: null,
            rruleResult: null,
            rrule: null,
        };
    },
    beforeMount: function() {
        this.frequencyIntervals = [];
        for(var i = 1; i <= 30; i++) {
            this.frequencyIntervals.push({ value: i, label: i.toString() });
        }
    },
    computed: {
        isDaily: function() {
            if (this.frequency === 3 && this.isRecurring) {
                this.weekDays = [];
                this.setFrequencyIntervals(365);
                this.getRecurringRule();
                return true;
            }

			return false;
        },
        isMonthly: function() {
            if (this.frequency === 1 && this.isRecurring) {
                this.weekDays = [];
                this.setFrequencyIntervals(12);
                this.getRecurringRule();
                return true;
            }

            return false;
        },
        isWeekly: function() {
            if (this.frequency === 2 && this.isRecurring) {
                this.setFrequencyIntervals(52);
                this.getRecurringRule();
                return true;
            }

            return false;
        },
        isYearly: function() {
            if (this.frequency === 0 && this.isRecurring) {
                this.weekDays = [];
                this.setFrequencyIntervals(10);
                this.getRecurringRule();
                return true;
            }

            return false;
        },
    },
    watch: {
        eventType: function() {
            var self = this;
            if (this.eventType) {
                this.eventTypes.forEach((elem, key) => {
                    if (elem.value == self.eventType.value) {
                        self.eventTypeConfig = elem;
                    }
                });
            }
        },
        calendarId: function() {
            this.getEventTypes();
        },
        isRecurring: function() {
            if (!this.isRecurring) {
                this.rrule = null;
                this.rruleResult = null;
            }
        },
    },
    methods: {
        setFrequencyIntervals: function(end) {
            this.frequencyIntervals = [];
            for(var i = 1; i <= end; i++) {
                this.frequencyIntervals.push({ value: i, label: i.toString() });
            }
        },
        dismissModal() {
            $('#calendar-modal-add-event').modal('hide');
        },
        submitEvent() {
            var self = this;
            var postdata = {
                CalendarEvents: {
                    calendar_id: this.calendarId.value,
                    title: this.title,
                    content: this.content,
                    start_date: this.startDate,
                    end_date: this.endDate,
                    event_type: ((this.eventType) ? this.eventType.value : null),
                    is_recurring: this.isRecurring,
                    recurrence: [this.rrule],
                }
            };
            if (this.attendeesList.length) {
                postdata.CalendarEvents['calendar_attendees'] = {
                    '_ids' : []
                };

                this.attendeesList.forEach( (elem, key) => {
                    postdata.CalendarEvents.calendar_attendees._ids.push(elem.value);
                });
            }
            $.ajax({
                url: '/calendars/calendar-events/add',
                method:'POST',
                dataType: 'json',
                data: postdata
            }).then( (resp) => {
                self.$emit('event-saved', resp);
            });
        },
        searchAttendees(search, loading) {
            var self = this;

            if (search.length > 2) {
                loading(true);
                $.ajax({
                    url: '/calendars/calendar-attendees/lookup',
                    dataType: 'json',
                    method: 'get',
                    contentType: 'application/json',
                    accepts: {
                        json: 'application/json',
                    },
                    data: { term: search, calendarId: this.calendarId },
                }).then((resp) => {
                    if (resp.length) {
                        self.attendees = [];
                        resp.forEach((elem, key) => {
                            self.attendees.push({label: elem.text, value: elem.id});
                        });
                    }
                    loading(false);
                });
            }
        },
        getUntil(rtype, value) {
            this.untilOption = rtype;
            this.untilValue = value;
        },
        getFrequency(val) {
            this.frequency = val;
            if (!val) {
                this.getRecurringRule();
            }
        },
        getInterval(val) {
            this.interval = val;
            if (!val) {
                this.getRecurringRule();
            }
        },
        getWeekDays(val) {
            this.weekDays = val;
            if (!val) {
                this.getRecurringRule();
            }
        },
        setDateRange(startDate, endDate) {
            this.startDate = startDate;
            this.endDate = endDate;
        },
        getEventTypes() {
            var self = this;
			this.eventTypes = [];
			this.eventTypesList = [];

            if (this.calendarId.value) {
                $.ajax({
                    url: '/calendars/calendar-events/get-event-types',
                    data: { calendar_id: this.calendarId.value },
                    dataType: 'json',
                    method: 'post',
                }).done(function(types) {
                    if (types.length) {
                        types.forEach( (elem, key) => {
                            self.eventTypes.push(elem);
                            self.eventTypesList.push({label: elem.name, value: elem.value})
                        });

                        //pre-select default event type if any.
                        self.eventType = self.eventTypesList[0];
                    }
                });
            }
        },
        getRecurringRule: function () {
            if (!this.isRecurring) {
                return null;
            }

            var byweekdays = [];
            var opts = { freq: this.frequency };

            if (this.weekDays.length) {
                this.weekDays.forEach( (day, k) => {
                    byweekdays.push(RRule[day]);
                });

                opts.byweekday = byweekdays;
            }

            if (this.interval) {
                opts.interval = this.interval;
            }

            if (this.untilOption == 'occurrence' && this.untilValue) {
                opts.count = this.untilValue;
            }

            if (this.untilOption == 'date' && this.untilValue) {
                opts.until = moment(this.untilValue).toDate();
            }

            var rule = new RRule(opts);

            this.rruleResult = rule.toText();
            this.rrule = 'RRULE:' + rule.toString();
        }
    },
});


var calendarApp = new Vue({
    el: '#qobo-calendar-app',
    data: {
        ids: [],
        events: [],
        calendars: [],
        calendarsList: [],
        editable: false,
        start: null,
        end: null,
        timezone: null,
        eventClick: null,
        public: null,
    },
    computed: {
        isIntervalChanged: function() {
            return [this.start, this.end].join('');
        },
    },
    watch: {
        calendars: function() {
            var self = this;
            this.calendarsList = [];
            if (this.calendars) {
               this.calendars.forEach((elem, key) => {
                    if (elem.permissions.edit) {
                        self.calendarsList.push( { value: elem.id, label: elem.name } );
                    }
               });
            }
        },
        isIntervalChanged: function() {
            var self = this;
            if (this.ids.length) {
                self.events = [];
                this.ids.forEach( function(calendarId, key) {
                    self.getEvents(calendarId);
                });
            }
        },
    },
    beforeMount: function() {
        this.start = this.$el.attributes.start.value;
        this.end = this.$el.attributes.end.value;
        this.timezone = this.$el.attributes.timezone.value;
        if (this.$el.attributes.public) {
            this.public = this.$el.attributes.public.value;
        }

        if (this.public == 'true') {
            this.getPublicCalendars();
        } else {
            this.getCalendars();
        }
    },
    methods: {
        updateStartEnd: function (start, end) {
            this.start = start;
            this.end = end;
        },
        getCalendars: function() {
            var self = this;
            var postdata = {};

            $.ajax({
                method: 'post',
                dataType: 'json',
                url: '/calendars/calendars/index',
            }).done( function(resp) {
                self.calendars = resp;
            });
        },
        getPublicCalendars: function() {
            var self = this;
            var postdata = {};

            $.ajax({
                method: 'post',
                dataType: 'json',
                url: '/calendars/calendars/index',
                data: {public: self.public},
            }).done( function(resp) {
                self.calendars = resp;
                self.calendars.forEach( function(elem, key) {
                    if (elem.active == true && elem.is_public == true) {
                        self.ids.push(elem.id);
                        self.getEvents(elem.id);
                    }
                });
            });
        },
        getEvents: function(id) {
            var self = this;
            var url = '/calendars/calendar-events/index'; //: string
            $.ajax({
                method: 'POST',
                dataType: 'json',
                url: url,
                data: {
                    'calendar_id': id,
                    'period': {
                        'start_date': this.start,
                        'end_date': this.end,
                    },
                    'timezone': this.timezone,
                }
            }).then(function(resp){
                if (!resp) {
                    return;
                }

                var event_ids = self.events.map( (element) => {
                    return element.id;
                });

                resp.forEach(function (elem, index) {
                    if ( !event_ids.includes(elem.id) ) {
                        self.events.push(elem);
                    }
                });
            });
        },
        removeEvents: function(id) {
            this.events = this.events.filter( function (item) {
                if (item.calendar_id !== id) {
                    return item;
                }
            });
        },
        updateCalendarIds: function(state, id) {
            var self = this;
            var found = false;

            this.ids.forEach( function (elem, key) {
                if (elem == id) {
                    if (state === false ) {
                        self.ids.splice(key, 1);
                        self.removeEvents(id);
                    } else {
                        found = true;
                    }
                }
            });

            if (state === true && !found) {
                this.ids.push(id);
                this.getEvents(id);
            }
        },
        getEventInfo: function(calendarEvent) {
            var url = '/calendars/calendar-events/view';
            var post = {
                id: calendarEvent.id,
                calendar_id: calendarEvent.calendar_id,
                event_type: calendarEvent.event_type
            };

            $.ajax({
                method: 'POST',
                url: url,
                data: post,
            }).done(function (resp) {
                if (resp) {
                    $('#calendar-modal-view-event').find('.modal-content').empty();
                    $('#calendar-modal-view-event').find('.modal-content').append(resp);
                    $('#calendar-modal-view-event').modal('toggle');
                }
            });
        },
        addCalendarEvent: function(date, event, view) {
            this.eventClick = date;
            $('#calendar-modal-add-event').modal('toggle');
        },
        addEventToResources(event) {
            if (event) {
                this.events.push(event);
                location.reload();
            }
        },
    }
});
// @codingStandardsIgnoreEnd
