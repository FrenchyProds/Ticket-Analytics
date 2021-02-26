<template>
    <div>
        <div v-if="loading">
        </div>
        <div v-else>
            <p class="chartTitle text-primary">Total Open & Closed Tickets</p>
            <q-toolbar class="text-center">
                <div class="xs-column">
                    <q-btn class="q-mb-1" icon="event" round color="primary">
                        <q-popup-proxy @before-show="updateStartDate" transition-show="scale" transition-hide="scale">
                            <q-date minimal navigation-min-year-month="2019/04" :navigation-max-year-month="dateLimit" v-model="startDate">
                                <div class="row items-center justify-end q-gutter-sm">
                                    <q-btn label="Cancel" color="primary" flat v-close-popup />
                                    <q-btn label="OK" color="primary" flat @click="save" v-close-popup />
                                </div>
                            </q-date>
                        </q-popup-proxy>
                    </q-btn>
                    <div class="flex content-between column-xs-sm row-md-lg-xl">
                        <p class="text-primary q-mb-none"><span class="dates">Start Date</span> : {{ startDate }}</p>
                        <p class="text-primary q-mb-none"><span class="dates">End Date</span> : {{ endDate }}</p>
                    </div>
                </div>
            </q-toolbar>
            <q-select label-color="primary" use-input outlined @input="onChangeProblem()" v-model="selectedProblem" label="Problems" @filter="filterFn" :options="problems">
                <template v-slot:no-option>
                    <q-item>
                        <q-item-section class="text-grey">
                        No results
                        </q-item-section>
                    </q-item>
                </template>
            </q-select>
            <q-select label-color="primary" use-input outlined @input="onChangeDetail()" v-model="selectedDetail" label="Details" @filter="filterFn" :options="details">
                <template v-slot:no-option>
                    <q-item>
                        <q-item-section class="text-grey">
                        No results
                        </q-item-section>
                    </q-item>
                </template>
            </q-select>
             <q-select label-color="primary" use-input outlined @input="onChangeCompany()" v-model="selectedCompany" label="Company" @filter="filterFn" :options="companies">
                <template v-slot:no-option>
                    <q-item>
                        <q-item-section class="text-grey">
                        No results
                        </q-item-section>
                    </q-item>
                </template>
            </q-select>
            <div class="chartRender" v-if="openTickets || closedTickets != 0">
                <apexchart id="ticketPieChart" type="pie" height="300" width="100%" :options="chartOptions" :series="series"></apexchart>
            </div>
            <div class="emptyContent" v-else>
                <p>There are no tickets of this type !</p>
            </div>
        </div>
    </div>
</template>

<script>

import { date } from 'quasar'

export default {
  name: 'ticketPieChart',
  data () {
    return {
        problems: [],
        series: ['', ''],
        details: [],
        selectedProblem: '',
        companies: [],
        routeCompany: '',
        selectedCompany: '',
        routeProblem: '',
        selectedDetail: '',
        routeDetail: '',
        openTickets: 0,
        closedTickets: 0,
        currentDate: '',
        loading: true,
        startDate: '',
        endDate: '',
        currentDateMinus: '',
        dateLimit: '',
        dataUrl: 'http://192.168.8.85:8000/dashboard/ticketpiechart/',
        paramRoute: '',
        chartOptions: {},
    }
  },
  mounted() {
    this.currentDate = new Date();
    this.currentDate = date.formatDate(this.currentDate, 'dddd MMM DD YYYY')
    this.dateLimit = date.formatDate(this.currentDate, 'YYYY/MM')
    this.endDate = date.formatDate(this.currentDate, 'YYYY-MM-DD')
    this.currentDateMinus = date.subtractFromDate(this.currentDate, { days: 7 })
    this.startDate = date.formatDate(this.currentDateMinus, 'YYYY-MM-DD')
    this.paramRoute = '%/%/%/' + this.startDate + '/' + this.endDate
    this.fetchData()
    this.loading = false;
  },
  methods: {
      fetchData: async function() {
          try {
            this.$axios.get(this.dataUrl + this.paramRoute  , {
                headers: { 'Content-Type': 'application/json' },
                crossdomain: true
                }
                ).then(res => {
                const getProblems = res.data.problems
                const getDetails = res.data.details
                this.details = []
                this.problems = []
                for (let i = 0; i < getProblems.length; i++) {
                    if(this.problems.indexOf(getProblems[i]) == -1) {
                        this.problems.push(getProblems[i].name)
                    }
                }
                if(getDetails) {
                    for (let i = 0; i < getDetails.length; i++) {
                        this.details.push(getDetails[i].name)
                    }
                } else {
                    this.details = [];
                }
                this.chartOptions = {
                    chart: {
                        type: 'pie',
                        toolbar: {
                            show: true
                        }
                    },
                        title: {
                            text: 'Weekly Ticket Tracker',
                            align: 'center',
                            style: {
                                color: '#06519C'
                            }
                        },
                        labels: ['Open Tickets', 'Closed Tickets'],
                        colors: ['#42A62A', '#06519C'],
                        responsive: [{
                            breakpoint: 480,
                            total: {
                                show: true
                            },
                            options: {
                                chart: {
                                    width: 250
                                },
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }],
                        legend: {
                            labels: {
                                colors: ['#42A62A', '#06519C'],
                            }
                        }
                }
                this.series[0] = res.data.openTicketDetail.length
                this.openTickets = res.data.openTicketDetail.length
                this.series[1] = res.data.closedTicketDetail.length
                this.closedTickets = res.data.closedTicketDetail.length
                for (let i = 0; i < res.data.openTicketDetail.length; i++) {
                    if(this.companies.indexOf(res.data.openTicketDetail[i].companyName) == -1) {
                        this.companies.push(res.data.openTicketDetail[i].companyName)
                    }
                }
                for (let i = 0; i < res.data.closedTicketDetail.length; i++) {
                    if(this.companies.indexOf(res.data.closedTicketDetail[i].companyName) == -1) {
                        this.companies.push(res.data.closedTicketDetail[i].companyName)
                    }
                }
            })
        } catch (err) {
            alert('Oups, something went wrong !' + err)
        }
      },

      fetchByProblem: async function() {
          try {
            if(!this.selectedProblem) {
                this.routeProblem = '%'
            } else {
                this.routeProblem = this.selectedProblem
            }
            if(this.selectedDetail != "") {
                this.selectedDetail = ""
            }
            if(!this.selectedCompany) {
                this.routeCompany = "%"
            } else {
                this.routeCompany = this.selectedCompany
            }
                this.$axios.get(this.dataUrl + this.routeProblem +  '/%/' + this.routeCompany + '/' + date.formatDate(this.startDate, 'YYYY-MM-DD HH:mm:ss') + '/' + date.formatDate(this.endDate, 'YYYY-MM-DD HH:mm:ss')  , {
                headers: { 'Content-Type': 'application/json' },
                crossdomain: true
                }
                ).then(res => {
                const getProblems = res.data.problems
                const getDetails = res.data.details
                this.problems = []
                this.details = []
                for (let i = 0; i < getProblems.length; i++) {
                    if(this.problems.indexOf(getProblems[i]) == -1) {
                        this.problems.push(getProblems[i].name)
                    }
                }
                if(getDetails) {
                    for (let i = 0; i < getDetails.length; i++) {
                        this.details.push(getDetails[i].name)
                    }
                } else {
                    this.details = [];
                }
                this.chartOptions = {
                        labels: ['Open Tickets', 'Closed Tickets'],
                        colors: ['#42A62A', '#06519C'],
                        legend: {
                            labels: {
                                colors: ['#42A62A', '#06519C'],
                            }
                        }
                    }
                this.series[0] = res.data.openTicketDetail.length
                this.openTickets = res.data.openTicketDetail.length
                this.series[1] = res.data.closedTicketDetail.length
                this.closedTickets = res.data.closedTicketDetail.length
                this.companies = []
                for (let i = 0; i < res.data.openTicketDetail.length; i++) {
                    if(this.companies.indexOf(res.data.openTicketDetail[i].companyName) == -1) {
                        this.companies.push(res.data.openTicketDetail[i].companyName)
                    }
                }
                for (let i = 0; i < res.data.closedTicketDetail.length; i++) {
                    if(this.companies.indexOf(res.data.closedTicketDetail[i].companyName) == -1) {
                        this.companies.push(res.data.closedTicketDetail[i].companyName)
                    }
                }
            })
        } catch (err) {
            alert('Oups, something went wrong !' + err)
        }
      },

      fetchByDetail: async function() {
          try {
            if(!this.selectedProblem) {
                this.routeProblem = '%'
            } else {
                this.routeProblem = this.selectedProblem
            }
            if(!this.selectedDetail) {
                this.routeDetail = '%'
            } else {
                this.routeDetail = this.selectedDetail
            } 
            if(!this.selectedCompany) {
                this.routeCompany = "%"
            } else {
                this.routeCompany = this.selectedCompany
            }
            this.$axios.get(this.dataUrl + this.routeProblem +  '/' + this.routeDetail + '/' + this.routeCompany + '/' + date.formatDate(this.startDate, 'YYYY-MM-DD HH:mm:ss') + '/' + date.formatDate(this.endDate, 'YYYY-MM-DD HH:mm:ss')  , {
            headers: { 'Content-Type': 'application/json' },
            crossdomain: true
                }).then(res => {
                const getProblems = res.data.problems
                const getDetails = res.data.details
                this.problems = []
                this.details = []
                for (let i = 0; i < getProblems.length; i++) {
                    this.problems.push(getProblems[i].name)
                }
                if(getDetails) {
                    for (let i = 0; i < getDetails.length; i++) {
                        this.details.push(getDetails[i].name)
                    }
                } else {
                    this.details = [];
                }
                this.chartOptions = {
                        labels: ['Open Tickets', 'Closed Tickets'],
                        colors: ['#42A62A', '#06519C'],
                        legend: {
                            labels: {
                                colors: ['#42A62A', '#06519C'],
                            }
                        }
                    }
                this.series[0] = res.data.openTicketDetail.length
                this.openTickets = res.data.openTicketDetail.length
                this.series[1] = res.data.closedTicketDetail.length
                this.closedTickets = res.data.closedTicketDetail.length
                this.companies = []
                for (let i = 0; i < res.data.openTicketDetail.length; i++) {
                    if(this.companies.indexOf(res.data.openTicketDetail[i].companyName) == -1) {
                        this.companies.push(res.data.openTicketDetail[i].companyName)
                    }
                }
                for (let i = 0; i < res.data.closedTicketDetail.length; i++) {
                    if(this.companies.indexOf(res.data.closedTicketDetail[i].companyName) == -1) {
                        this.companies.push(res.data.closedTicketDetail[i].companyName)
                    }
                }
            })
        } catch (err) {
            alert('Oups, something went wrong !' + err)
        }           
      },

    save: async function() {
      this.endDate = date.addToDate(this.startDate, { days: 7 })
      this.endDate = date.formatDate(this.endDate, 'YYYY/MM/DD')
      try {
        if(!this.selectedProblem) {
            this.routeProblem = '%'
        } else {
            this.routeProblem = this.selectedProblem
        }
        if(!this.selectedDetail) {
            this.routeDetail = '%'
        } else {
            this.routeDetail = this.selectedDetail
        }
        if(!this.selectedCompany) {
                this.routeCompany = "%"
        } else {
            this.routeCompany = this.selectedCompany
        }
        this.paramRoute = this.routeProblem + '/' + this.routeDetail + '/' + this.routeCompany + '/' + date.formatDate(this.startDate, 'YYYY-MM-DD HH:mm:ss') + '/' + date.formatDate(this.endDate, 'YYYY-MM-DD HH:mm:ss')
        this.$axios.post(this.dataUrl + this.paramRoute  , {
          headers: { 'Content-Type': 'application/json' },
          crossdomain: true
          }
          ).then(
            this.chartOptions = {
                labels: ['Open Tickets', 'Closed Tickets'],
                    colors: ['#42A62A', '#06519C'],
                    legend: {
                        labels: {
                            colors: ['#42A62A', '#06519C'],
                        }
                    }
            },
            this.companies = [],
            this.fetchData(this.paramRoute))
      } catch(error) {
          alert('Oups, something went wrong !' + error)
      }
    },

    updateStartDate: async function() {
        this.startDate = this.startDate
    },

    filterFn (val, update, abort) {
      if (val === '') {
        update(() => {
          this.users = this.options
        })
        return
      }
      update(() => {
        const needle = val.toLowerCase()
        this.users = this.options.filter(v => v.toLowerCase().indexOf(needle) > -1)
      })
    },

    onChangeProblem: async function() {
          try {
            if(!this.selectedProblem) {
                this.routeProblem = '%'
            } else {
                this.routeProblem = this.selectedProblem;
            }
            if(!this.selectedCompany) {
                this.routeCompany = "%"
            } else {
                this.routeCompany = this.selectedCompany
            }
            await this.$axios.post(this.dataUrl + this.routeProblem +  '/%/' + this.routeCompany + '/' + date.formatDate(this.startDate, 'YYYY-MM-DD HH:mm:ss') + '/' + date.formatDate(this.endDate, 'YYYY-MM-DD HH:mm:ss'), 
            ).then(this.fetchByProblem())
        } catch (err) {
            alert('Oups, something went wrong !' + err)
        }
    },

    onChangeDetail: async function() {
        try {
            if(!this.selectedDetail) {
                this.routeDetail = '%'
            } else {
                this.routeDetail = this.selectedDetail;
            }
            if(!this.selectedCompany) {
                this.routeCompany = "%"
            } else {
                this.routeCompany = this.selectedCompany
            }
            await this.$axios.post(this.dataUrl + this.routeProblem +  '/' + this.routeDetail + '/' + this.routeCompany + '/' + date.formatDate(this.startDate, 'YYYY-MM-DD HH:mm:ss') + '/' + date.formatDate(this.endDate, 'YYYY-MM-DD HH:mm:ss'),
            ).then(this.fetchByDetail())
        } catch (err) {
            alert('Oups, something went wrong !' + err)
        }
      },

      onChangeCompany: async function() {
          try {
            if(!this.selectedProblem) {
                this.routeProblem = '%'
            } else {
                this.routeProblem = this.selectedProblem;
            }
            if(!this.selectedDetail) {
                this.routeDetail = '%'
            } else {
                this.routeDetail = this.selectedDetail;
            }
            if(!this.selectedCompany) {
                this.routeCompany = "%"
            } else {
                this.routeCompany = this.selectedCompany
            }
            this.paramRoute = this.routeProblem + '/' + this.routeDetail + '/' + this.routeCompany + '/' + date.formatDate(this.startDate, 'YYYY-MM-DD HH:mm:ss') + '/' + date.formatDate(this.endDate, 'YYYY-MM-DD HH:mm:ss')
        this.$axios.post(this.dataUrl + this.paramRoute  , {
          headers: { 'Content-Type': 'application/json' },
          crossdomain: true
          }
          ).then(
            this.chartOptions = {
                labels: ['Open Tickets', 'Closed Tickets'],
                    colors: ['#42A62A', '#06519C'],
                    legend: {
                        labels: {
                            colors: ['#42A62A', '#06519C'],
                        }
                    }
            },
            this.fetchData(this.paramRoute))
          } catch (error) {
              alert('Oups, something went wrong !' + error)
          }
      },

    spinner() {
        if(this.loading === true) {
            this.$q.loading.show({
                backgroundColor: 'purple-10',
                delay: 0
            })
        } else {
            this.$q.loading.hide()
        }
    },
  }
}
</script>

<style>
form:nth-child(odd) {
    padding-bottom: 1rem;
}

.apexcharts-legend-text {
    color: #06519C !important;
}

.apexcharts-canvas {
    width: 100% !important;
    text-align: center;
}

.apexcharts-canvas svg {
    margin: auto;
}

.chartTitle {
    color: #06519C;
    font-size: 1.4rem;
    text-align: center;
    text-transform: uppercase;
}

.chartRender {
    padding-top: 2rem;
}

.emptyContent {
  padding: 20px;
  background-color: #f44336; /* Red */
  color: white;
  text-align: center;
  font-size: 1.3rem;
  align-self: center;
}

.emptyContent p {
    margin: auto;
    line-height: 1.4;
}

</style>