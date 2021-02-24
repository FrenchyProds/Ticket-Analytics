<template>
  <div class="q-pa-md">
      <div class="q-gutter-sm center">
          <div class="row customRowPosition justify-center items-center">
              <div class="xs-column">
                <div class="datePicker">
                <q-btn class="q-mb-1" icon="event" round color="blue">
                    <q-popup-proxy @before-show="updateStartDate" transition-show="scale" transition-hide="scale">
                        <q-date minimal navigation-min-year-month="2019/04" :navigation-max-year-month="dateLimit" v-model="startDate">
                            <div class="row items-center justify-end q-gutter-sm">
                                <q-btn label="Cancel" color="#42A62A" flat v-close-popup />
                                <q-btn label="OK" color="#42A62A" flat @click="save" v-close-popup />
                            </div>
                        </q-date>
                    </q-popup-proxy>
                </q-btn>
                <q-btn class="q-mb-1" icon="fas fa-calendar-times" round color="blue">
                    <q-popup-proxy @before-show="updateEndDate" transition-show="scale" transition-hide="scale">
                        <q-date minimal navigation-min-year-month="2019/04" :navigation-max-year-month="dateLimit" v-model="endDate">
                            <div class="row items-center justify-end q-gutter-sm">
                                <q-btn label="Cancel" color="primary" flat v-close-popup />
                                <q-btn label="OK" color="primary" flat @click="save" v-close-popup />
                            </div>
                        </q-date>
                    </q-popup-proxy>
                </q-btn>
                </div>
                    <div class="flex content-between column-xs-sm row-md-lg-xl datePicker">
                        <p class="text-white q-mb-none"><span class="dates">Start Date</span> : {{ startDate }}</p>
                        <p class="text-white q-mb-none"><span class="dates">End Date</span> : {{ endDate }}</p>
                    </div>
                </div>
          <q-btn  label="Toggle Columns" color="primary" @click="modal = true" class="buttonStyle customButtonPosition center" />
          </div>
          <q-dialog v-model="modal">
            <q-card>
              <q-card-section class="modalStyle column justify-center">
                <q-btn icon="dangerous" color="red" class="row justify-center items-center" @click="modal = false"/>
                <q-checkbox dense v-model="visibleColumns" val="problem" label="Problem Name" />
                <q-separator/>
                <q-checkbox dense v-model="visibleColumns" val="detail" label="Detail Name" />
                <q-separator/>
                <q-checkbox dense v-model="visibleColumns" val="company" label="Company Name" />
                <q-separator/>
                <q-checkbox dense v-model="visibleColumns" val="ticketLevel" label="Ticket Level" />
                <q-separator/>
                <q-checkbox dense v-model="visibleColumns" val="ticketStatus" label="Ticket Status" />
                <q-separator/>
                <q-checkbox dense v-model="visibleColumns" val="created" label="Date Created" />
                <q-separator/>
                <q-checkbox dense v-model="visibleColumns" val="closed" label="Date Closed" />
                <q-separator/>
                <q-checkbox dense v-model="visibleColumns" val="age" label="Ticket Age (in days)" />
                <q-separator/>
                <q-checkbox dense v-model="visibleColumns" val="comment" label="Ticket Comment" />
                <q-separator/>
                <q-checkbox dense v-model="visibleColumns" val="createdBy" label="Created By" />
                <q-separator/>
                <q-checkbox dense v-model="visibleColumns" val="closedBy" label="Closed By" />
                <q-separator/>
                <q-btn icon="delete" class="row justify-center items-center" color="secondary" @click="removeColumns" label="Remove All Columns" />
                <q-btn icon="settings_backup_restore" class="row justify-center items-center" color="primary" @click="resetColumns" label="Reset Columns" />
                <q-separator/>
              </q-card-section>
            </q-card>
          </q-dialog>
      </div>
    <q-grid
    :data="data" 
    :columns="columns"
    separator="cell"
    :global_search="true"
    :file_name="fileName"
    :fullscreen="true"
    :groupby_filter="true"
    :csv_download="true"
    :visible_columns="visibleColumns"
    :columns_filter="true"
    :pagination.sync="pagination"
    >
    </q-grid>
  </div>
</template>

<script>
// #06519C == Blue
// #42A62A == Green
import { date } from 'quasar'

export default {
  data () {
    return {
        url: 'http://192.168.8.85:8000/dashboard/rawdata/',
        paramRoute: '%/%',
        visibleColumns: ['ticketId', 'problem', 'detail', 'company', 'ticketLevel', 'ticketStatus', 'created', 'closed', 'age', 'comment', 'createdBy', 'closedBy'],
        reset: '',
        startDate: '',
        endDate: '',
        dateLimit: '',
        ageCalc: '',
        closedBy: '',
        dateClosed: '',
        currentDate: '',
        fileName: '',
        pagination: {
            rowsPerPage: 10,
        },
        modal: false,
        columns: [
        {
            name: 'ticketId',
            required: true,
            label: 'Ticket Id',
            align: 'left',
            field: 'id',
            sortable: true,
            sort: (a, b) => parseInt(a, 10) - parseInt(b, 10)
        },
        {
            name: 'problem',
            align: 'center',
            label: 'Problem Name',
            field: 'problem',
            sortable: true,
            grouping: true
        },
        {
            name: 'detail', 
            label: 'Detail Name', 
            field: 'detail', 
            sortable: true, 
            grouping: true
        },
        {
            name: 'company', 
            label: 'Company Name', 
            field: 'company', 
            sortable: true, 
            grouping: true,
        },
        {
            name: 'ticketLevel', 
            label: 'Ticket Level', 
            field: 'ticketLevel',
            grouping: true
        },
        {
            name: 'ticketStatus', 
            label: 'Ticket Status', 
            field: 'ticketStatus',
            sortable: true,
            grouping: true,
            classes: ''
        },
        {
            name: 'created',
            label: 'Date Created',
            field: 'created',
            sortable: true,
            sort: (a, b) => parseInt(a, 10) - parseInt(b, 10),
        },
        {
            name: 'closed',
            label: 'Date Closed',
            field: 'closed',
            sortable: true,
            sort: (a, b) => parseInt(a, 10) - parseInt(b, 10)
        },
        {
            name: 'age',
            label: 'Ticket Age',
            field: 'age',
            sortable: true
        },
        {
            name: 'comment',
            label: 'Ticket Comment',
            field: 'comment',
            sortable: false,
            style: 'max-width: 300px; overflowX: auto; word-break: break;',
            align: 'left',
        },
        {
            name: 'createdBy',
            label: 'Created By',
            field: 'createdBy',
            sortable: true,
            grouping: true
        },
        {
            name: 'closedBy',
            label: 'Closed By',
            field: 'closedBy',
            sortable: true,
            grouping: true
        }
    ],
    data: []
    }
  },
  mounted() {
    this.currentDate = new Date()
    this.dateLimit = date.formatDate(this.currentDate, 'YYYY/MM')
    this.endDate = date.formatDate(this.currentDate, 'YYYY-MM-DD')
    this.startDate = '2019-04-19'
    this.currentDate = date.formatDate(this.currentDate, 'DD-MM-YYYY HH-mm-ss')
    this.fileName = "raw_data_" + this.currentDate
    this.paramRoute = this.startDate + '/' + this.endDate
    this.fetchData();
  },
  methods: {
      resetColumns() {
          this.visibleColumns = ['ticketId', 'problem', 'detail', 'company', 'ticketLevel', 'ticketStatus', 'created', 'closed', 'age', 'comment', 'createdBy', 'closedBy'];
      },
      removeColumns() {
          this.visibleColumns = ['ticketId'];
      },
      fetchData() {
          try {
            this.$axios.get(this.url + this.paramRoute, {
                headers: { 'Content-Type': 'application/json' },
                crossdomain: true
            }).then(res => {
                console.log(this.url + this.paramRoute)
                let fetchData = res.data.response
                let unit = 'days'
                for (let i = 0; i < fetchData.length; i++) {
                    if(fetchData[i].dateClosed) {
                        this.ageCalc = date.getDateDiff(fetchData[i].dateClosed, fetchData[i].dateCreated, unit) + ' days'
                        this.dateClosed = fetchData[i].dateClosed
                    } else {
                        this.ageCalc = date.getDateDiff(this.currentDate, fetchData[i].dateCreated, unit) + ' days'
                        this.dateClosed = ""
                    }
                    if(fetchData[i].closedBy) {
                        this.closedBy = fetchData[i].closedBy
                    } else {
                        this.closedBy = ""
                    }
                    this.data.push({
                        id: fetchData[i].ticketId,
                        problem: fetchData[i].problemName,
                        detail: fetchData[i].detailName,
                        company: fetchData[i].companyName,
                        ticketLevel: 'Level ' + fetchData[i].ticketLevel,
                        ticketStatus: fetchData[i].ticketStatus,
                        created: fetchData[i].dateCreated,
                        createdBy: fetchData[i].createdBy,
                        closed: this.dateClosed,
                        closedBy: this.closedBy,
                        age: this.ageCalc,
                        comment: fetchData[i].ticketSubject
                    })
                }
                console.log(this.data)
            })
          } catch (error) {
            alert('Oups, something went wrong !' + error)
          }
      },
    updateStartDate() {

    },
    updateEndDate() {

    },
    save() {
        try {
            this.paramRoute = date.formatDate(this.startDate, 'YYYY-MM-DD') + '/' + date.formatDate(this.endDate, 'YYYY-MM-DD')
            this.$axios.post(this.url + this.paramRoute  , {
                headers: { 'Content-Type': 'application/json' },
                crossdomain: true
                }
                ).then( res => {
                    this.data.splice(0, this.data.length)
                    console.log(this.data)
                    this.fetchData(this.paramRoute)
                })
        } catch (error) {
            alert('Oups, something went wrong !' + error)
        }
    },
  }
}
</script>

<style lang="scss">

    .q-table .openTicket {
        background-color: lightgreen;
    }
    .q-table .closedTicket {
        background-color: lightcoral;
    }

    .datePicker {
        display: flex;
        justify-content: space-around;
        padding-bottom: 1rem;
    }

    @media (min-width: 768px) {
        .customRowPosition {
            display: block;
        }
        .customButtonPosition {
            position: relative;
            top: 8vh;
            z-index: 1;
        }
    }

    @media (min-width: 1024px) {
        .customButtonPosition {
            top: 7vh;
        }
    }

    @media (min-width: 1440px) {
        .customButtonPosition {
            top: 5.5vh;
        }
    }

    @media (min-width: 2560px) {
        .customButtonPosition {
            top: 3.7vh;
        }
    }

    .modalStyle {
        & .q-btn {
            margin-bottom: 0.5rem;
        }
        padding-left: 2rem;
        & .q-checkbox--dense {
            margin-bottom: 0.3rem;
        }
        & .q-checkbox__inner {
            margin-right: 0.5rem;
        }
        & .q-checkbox__label {
            line-height: 1.5;
            padding: 0.3rem 0rem;
        } 
    }


</style> 