<template>
    <div>
        <div v-if="loading">
        </div>
        <div v-else>
            <p class="chartTitle">Total open and closed tickets up to {{currentDate}}</p>
            <form method="get" name="selectProblem">
                <select v-model="selectedProblem" @change="onChange()" class="problems" name="problems">
                    <option selected value="All">All</option>
                        <option v-for="problem in problems" :key="problem.id" class="optionTrigger" :value="(problem.id)">{{ problem.name }}</option>
                </select>
            </form>
            <form v-if="details.length != 0" method="get" name="selectDetail">
                <select v-model="selectedDetail" id="pbDetail" @change="onChangeDetail()" name="problemDetail">
                    <option value="All">All</option>
                    <option class="optionTrigger" v-for="detail in details" :key="detail.id" :value="(detail.id)">{{ detail.name }}</option>
                </select>
            </form>
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
        selectedProblem: 'All',
        routeProblem: '',
        selectedDetail: 'All',
        routeDetail: '',
        openTickets: 0,
        closedTickets: 0,
        currentDate: '',
        loading: true,
        dataUrl: 'http://192.168.8.85:8000/dashboard/ticketpiechart/',
        chartOptions: {
            chart: {
            type: 'pie',
            toolbar: {
                show: true
            }
            },
            title: {
            text: 'Ticket Tracker',
            align: 'center',
            style: {
                color: '#FFF'
            }
            },
            labels: ['Open Tickets', 'Closed Tickets'],
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
                colors: '#FFF'
            }
            }
        }
    }
},
  mounted () {
    this.fetchData()
    this.currentDate = new Date();
    this.currentDate = date.formatDate(this.currentDate, 'dddd MMM DD YYYY')
    this.loading = false;
  },
  methods: {
      fetchData: async function() {
          try {
            this.$axios.get(this.dataUrl + '%/%'  , {
                headers: { 'Content-Type': 'application/json' },
                crossdomain: true
                }
                ).then(res => {
                const getProblems = res.data.problems
                const getDetails = res.data.details
                for (let i = 0; i < getProblems.length; i++) {
                    this.problems = getProblems
                }
                if(getDetails) {
                    for (let i = 0; i < getDetails.length; i++) {
                        this.details = getDetails
                    }
                } else {
                    this.details = [];
                }
                this.chartOptions = {
                    chart: {
                        type: 'pie',
                        toolbar: {
                            show: true
                        },
                        },
                        labels: ['Open Tickets', 'Closed Tickets'],
                        colors: ['#42A62A', '#f44336'],
                        responsive: [{
                        breakpoint: 480,
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
                            colors: '#FFF'
                        }
                        }
                    }
                this.series[0] = res.data.openTickets
                this.openTickets = res.data.openTickets
                this.series[1] = res.data.closedTickets
                this.closedTickets = res.data.closedTickets
            })
        } catch (err) {
            alert('Oups, something went wrong !')
        }
      },

      fetchByProblem: async function() {
          try {
            if(this.selectedProblem === 'All') {
            this.routeProblem = '%'
                } else {
                    this.routeProblem = this.selectedProblem
                }
            if(this.selectedDetail != "All") {
                this.selectedDetail = "All"
            }
                this.$axios.get(this.dataUrl + this.routeProblem +  '/%'  , {
                headers: { 'Content-Type': 'application/json' },
                crossdomain: true
                }
                ).then(res => {
                const getProblems = res.data.problems
                const getDetails = res.data.details
                for (let i = 0; i < getProblems.length; i++) {
                    this.problems = getProblems
                }
                if(getDetails) {
                    for (let i = 0; i < getDetails.length; i++) {
                        this.details = getDetails
                    }
                } else {
                    this.details = [];
                }
                this.chartOptions = {
                    chart: {
                        type: 'pie',
                        toolbar: {
                            show: true
                        },
                        },
                        labels: ['Open Tickets', 'Closed Tickets'],
                        colors: ['#42A62A', '#f44336'],
                        responsive: [{
                        breakpoint: 480,
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
                            colors: '#FFF'
                        }
                        }
                    }
                this.series[0] = res.data.openTickets
                this.openTickets = res.data.openTickets
                this.series[1] = res.data.closedTickets
                this.closedTickets = res.data.closedTickets
            })
        } catch (err) {
            alert('Oups, something went wrong !')
        }
      },

      fetchByDetail: async function() {
          try {
            if(this.selectedDetail === 'All' || this.details[0].parent != this.routeProblem) {
            this.routeDetail = '%'
                } else {
                    this.routeDetail = this.selectedDetail
                } this.$axios.get(this.dataUrl + this.routeProblem +  '/' + this.routeDetail  , {
                    headers: { 'Content-Type': 'application/json' },
                    crossdomain: true
                }).then(res => {
                const getProblems = res.data.problems
                const getDetails = res.data.details
                for (let i = 0; i < getProblems.length; i++) {
                    this.problems = getProblems
                }
                if(getDetails) {
                    for (let i = 0; i < getDetails.length; i++) {
                        this.details = getDetails
                    }
                } else {
                    this.details = [];
                }
                this.chartOptions = {
                    chart: {
                        type: 'pie',
                        toolbar: {
                            show: true
                        },
                        },
                        labels: ['Open Tickets', 'Closed Tickets'],
                        colors: ['#42A62A', '#f44336'],
                        responsive: [{
                        breakpoint: 480,
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
                            colors: '#FFF'
                        }
                        }
                    }
                this.series[0] = res.data.openTickets
                this.openTickets = res.data.openTickets
                this.series[1] = res.data.closedTickets
                this.closedTickets = res.data.closedTickets
            })
        } catch (err) {
            alert('Oups, something went wrong !' + err)
        }           
      },

    onChange: async function() {
          try {
            // this.routeDetail = '%'
            // this.selectedDetail = '%'
            if(this.selectedProblem === 'All') {
                this.routeProblem = '%'
            } else {
                this.routeProblem = this.selectedProblem;
            }
            await this.$axios.post(this.dataUrl + this.routeProblem +  '/%', 
            ).then(this.fetchByProblem())
        } catch (err) {
            alert('Oups, something went wrong !' + err)
        }
    },

    onChangeDetail: async function() {
        try {
            if(this.selectedDetail === 'All') {
                this.routeDetail = '%'
            } else {
                this.routeDetail = this.selectedDetail;
            }
            await this.$axios.post(this.dataUrl + this.routeProblem +  '/' + this.routeDetail, 
            ).then(this.fetchByDetail()) 
        } catch (err) {
            alert('Oups, something went wrong !' + err)
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
    color: white !important;
}

.apexcharts-canvas {
    width: 100% !important;
    text-align: center;
}

.apexcharts-canvas svg {
    margin: auto;
}

.chartTitle {
    color: white;
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