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
                <option selected value="All">All</option>
                    <option class="optionTrigger" v-for="detail in details" :key="detail.id" :value="(detail.id)">{{ detail.name }}</option>
            </select>
        </form>
        <div v-if="openTickets || closedTickets != 0">
        <apexchart id="ticketPieChart" type="pie" height="230" :options="chartOptions" :series="series"></apexchart>
        </div>
        <div v-else>
            There are either no open or closed tickets of this type !
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
          if(this.selectedProblem === 'All') {
          this.routeProblem = '%'
          this.routeDetail = '%'
            } else {
                this.routeProblem = this.selectedProblem
            }
            if(this.selectedDetail === 'All') {
                this.routeDetail = '%'
            } else {
                this.routeDetail = this.selectedDetail
            }
            this.$axios.get('http://192.168.8.85:8000/dashboard/getProblems/' + this.routeProblem +  '/' + this.routeDetail , {
            headers: { 'Content-Type': 'application/json' },
            crossdomain: true
            }
            ).then(res => {
            const getProblems = res.data.problems
            const getDetails = res.data.details
            console.log(res.data)
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
      },
    onChange: async function() {
          try {
            if(this.selectedProblem === 'All') {
                this.routeProblem = '%'
            } else {
                this.routeProblem = this.selectedProblem;
            }
            console.log('http://192.168.8.85:8000/dashboard/getProblems/' + this.routeProblem +  '/%')
            await this.$axios.post('http://192.168.8.85:8000/dashboard/getProblems/' + this.routeProblem +  '/%', 
            ).then(this.fetchData())
        } catch (err) {
            console.log(err);
        }
    },
    onChangeDetail: async function() {
        try {
            if(this.selectedDetail === 'All') {
                this.routeDetail = '%'
            } else {
                this.routeDetail = this.selectedDetail;
            }
            console.log('http://192.168.8.85:8000/dashboard/getProblems/' + this.routeProblem +  '/' + this.routeDetail)
            await this.$axios.post('http://192.168.8.85:8000/dashboard/getProblems/' + this.routeProblem +  '/' + this.routeDetail, 
            ).then(this.fetchData()) 
        } catch (err) {
            console.log(err)
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