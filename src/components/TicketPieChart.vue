<template>
    <div>
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
        <div v-if="openTickets || closedTickets != 0">
            <apexchart id="ticketPieChart" type="pie" height="230" :options="chartOptions" :series="series"></apexchart>
        </div>
        <div v-else>
            <p class="text-h6 text-center text-white">There are no tickets of this type !</p>
        </div>
    </div>
</template>

<script>

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
            align: 'left',
            style: {
                color: '#FFF'
            }
            },
            labels: ['Open Tickets', 'Closed Tickets'],
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
    }
},
  mounted () {
      this.fetchData()
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
                        title: {
                        text: 'Total open & closed tickets',
                        align: 'center',
                        style: {
                            color: '#FFF'
                        }
                        },
                        labels: ['Open Tickets', 'Closed Tickets'],
                        colors: ['#054206', '#AF0909'],
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
                        title: {
                        text: 'Total open & closed tickets',
                        align: 'center',
                        style: {
                            color: '#FFF'
                        }
                        },
                        labels: ['Open Tickets', 'Closed Tickets'],
                        colors: ['#054206', '#AF0909'],
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
                        title: {
                        text: 'Total open & closed tickets',
                        align: 'center',
                        style: {
                            color: '#FFF'
                        }
                        },
                        labels: ['Open Tickets', 'Closed Tickets'],
                        colors: ['#054206', '#AF0909'],
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
      }
  }
}
</script>

<style>
.apexcharts-legend-text {
    color: white !important;
}
</style>