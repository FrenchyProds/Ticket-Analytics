<template>
  <div class="q-pa-md">
      <div class="q-gutter-sm">
          <q-btn label="Toggle Columns" color="primary" @click="modal = true" />
          <q-dialog v-model="modal">
            <q-card>
              <q-card-section class="modalStyle">
                <q-checkbox dense v-model="visibleColumns" val="ticketId" label="Ticket Id" /><br />
                <q-separator/>
                <q-checkbox dense v-model="visibleColumns" val="problem" label="Problem Name" /><br />
                <q-separator/>
                <q-checkbox dense v-model="visibleColumns" val="detail" label="Detail Name" /><br />
                <q-separator/>
                <q-checkbox dense v-model="visibleColumns" val="company" label="Company Name" /><br />
                <q-separator/>
                <q-checkbox dense v-model="visibleColumns" val="ticketLevel" label="Ticket Level" /><br />
                <q-separator/>
                <q-checkbox dense v-model="visibleColumns" val="ticketStatus" label="Ticket Status" /><br />
                <q-separator/>
                <q-checkbox dense v-model="visibleColumns" val="created" label="Date Created" /><br />
                <q-separator/>
                <q-checkbox dense v-model="visibleColumns" val="closed" label="Date Closed" /><br />
                <q-separator/>
                <q-checkbox dense v-model="visibleColumns" val="age" label="Ticket Age (in days)" /><br />
                <q-separator/>
                <q-checkbox dense v-model="visibleColumns" val="comment" label="Ticket Comment" /><br />
                <q-separator/>
                <q-checkbox dense v-model="visibleColumns" val="createdBy" label="Created By" /><br />
                <q-separator/>
                <q-checkbox dense v-model="visibleColumns" val="closedBy" label="Closed By" /><br />
              </q-card-section>
            </q-card>
          </q-dialog>
      </div>
    <q-grid
    :data="data" 
    :columns="columns"
    separator="cell"
    :global_search="true"
    file_name="raw_data"
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
        url: 'http://192.168.8.85:8000/dashboard/rawdata/%/%/%/%/%/%/%/%/%',
        visibleColumns: ['ticketId', 'problem', 'detail', 'company', 'ticketLevel', 'ticketStatus', 'created', 'closed', 'age', 'comment', 'createdBy', 'closedBy'],
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
            grouping: true
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
            style: 'max-width: 300px; overflowX: scroll; word-break: break;',
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
    this.fetchData();
  },
  methods: {
      getRandomInt(max) {
        return Math.floor(Math.random() * Math.floor(max));
      },
      fetchData() {
          try {
            this.$axios.get(this.url, {
                headers: { 'Content-Type': 'application/json' },
                crossdomain: true
            }).then(res => {
                let newDate = new Date()
                let createdDate = date.subtractFromDate(newDate, { days: 15 })
                createdDate = date.formatDate(createdDate, 'DD-MM-YYYY')
                let closedDate = date.subtractFromDate(newDate, { days: 5 })
                closedDate = date.formatDate(closedDate, 'DD-MM-YYYY')
                let fetchData = res.data.response
                for (let i = 0; i < fetchData.length; i++) {
                    this.data.push({
                        id: fetchData[i].ticketId,
                        problem: fetchData[i].problemName,
                        detail: fetchData[i].detailName,
                        company: fetchData[i].companyName,
                        ticketLevel: fetchData[i].ticketLevel,
                        ticketStatus: fetchData[i].ticketStatus,
                        created: createdDate,
                        createdBy: fetchData[i].createdBy,
                        closed: closedDate,
                        closedBy: fetchData[i].closedBy,
                        age: closedDate - createdDate,
                        comment: fetchData[i].ticketSubject
                    })
                }
                console.log(this.data)
            })
          } catch (error) {
              console.log(error)
          }
      }
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

    .modalStyle {
        padding-left: 2rem;
        & .q-checkbox__inner {
            margin-right: 0.5rem;
        }
        & .q-checkbox__label {
            line-height: 1.5;
            padding: 0.3rem 0rem;
        } 
    }


</style> 