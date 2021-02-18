<template>
    <div>
      <p class="chartTitle">Company Ticket Tracker - Monthly</p>
        <q-toolbar class="text-center">
        <div class="xs-column">
            <q-btn class="q-mb-1" icon="event" round color="primary">
                <q-popup-proxy @before-show="updateStartDate()" transition-show="scale" transition-hide="scale">
                    <q-date minimal navigation-min-year-month="2019/07" v-model="startDate">
                        <div class="row items-center justify-end q-gutter-sm">
                            <q-btn label="Cancel" color="primary" flat v-close-popup />
                            <q-btn label="OK" color="primary" flat @click="save()" v-close-popup />
                        </div>
                    </q-date>
                </q-popup-proxy>
            </q-btn>
            <div class="flex content-between column-xs-sm row-md-lg-xl">
            <p class="text-white q-mb-none"><span class="dates">Start Date</span> : {{ startDate }}</p>
            
            <p class="text-white q-mb-none"><span class="dates">End Date</span> : {{ endDate }}</p>
        </div>
        </div>
        </q-toolbar>
        <q-select label-color="white" outlined @input="onChangeCompany()" v-model="selectedCompany" use-input input-debounce="0" label="Companies" @filter="filterFn()" :options="companies">
            <template v-slot:no-option>
                <q-item>
                    <q-item-section class="text-grey">
                    No results
                    </q-item-section>
                </q-item>
            </template>
        </q-select>
        <div class="emptyContent" v-if="empty != false">
          <p>There are no tickets for the selected dates</p>
          <p>Please try with another date and/or company</p>
        </div>
        <q-scroll-area
          v-if="empty == false"
          horizontal
          style="minHeight: 250px; height: 300px; width: 2000px; max-height: 100%; max-width: 100%;"
          class="bg-grey-1 rounded-borders shadow-2"
        >
        <card-base>
          <div style="width: 1200px; min-height: 200px; linear-gradient( 135deg, #343E59 10%, #2B2D3E 40%)">
          <apexchart v-if="empty == false" type="bar" height="250" :options="chartOptions" :series="series" />
          </div>
        </card-base>
        </q-scroll-area>
    </div>
</template>

<script>
import { date } from 'quasar'
import CardBase from 'components/CardBase'
export default {
  name: 'MonthlyCompanyGraph',
  components: {
    CardBase,
  },
  data () {
    return {
      selectedCompany: '',
      userRoute: '',
      companies: [],
      companyId: [],
      companyList: [],
      options: [],
      dateStart: '',
      startDate: '',
      endDate: '',
      currentDate: '',
      currentDateMinus: '',
      dataUrl: 'http://192.168.8.85:8000/dashboard/companygraph/',
      paramRoute: '%/%/%/%/%',
      series: '',
      empty: true,
      chartOptions: {}
    }
  },
    mounted() { 
        this.currentDate = new Date()
        this.endDate = date.formatDate(this.currentDate, 'YYYY-MM-DD')
        this.currentDateMinusWeek = date.subtractFromDate(this.currentDate, { days: 30 })
        this.startDate = date.formatDate(this.currentDateMinusWeek, 'YYYY-MM-DD')
        this.dateStart = this.startDate
        this.paramRoute = '%/%/%/' + this.startDate + '/' + this.endDate
        this.fetchData()
    },
    methods: {
        fetchData: async function() {
            try {
                this.$axios.get(this.dataUrl + this.paramRoute , {
                    headers: { 'Content-Type': 'application/json' },
                    crossdomain: true
                }).then(res => {
                    console.log(res)
                    const getCompanies = res.data.companyList
                    if(this.companies.length === 0) {
                        for (let i = 0; i < getCompanies.length; i++) {
                            this.companies = getCompanies
                        }
                    }
                    let open = res.data.openTickets
                    let closed = res.data.closedTickets
                    this.options = this.users
                    console.log(this.companies)
                })
            } catch (error) {
                console.log(error)
            }
        },

        filterFn (val, update, abort) {
            if (val === '') {
                update(() => {
                    this.companies = this.options
                })
                return
            }
            update(() => {
                const needle = val.toLowerCase()
                this.companies = this.options.filter(v => v.toLowerCase().indexOf(needle) > -1)
            })
        },

        updateStartDate () {
            this.startDate = this.dateStart
        },

        save() {

        },

        onChangeCompany() {

        }
    }
}