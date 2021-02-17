<template>
    <div>
      <p class="chartTitle">Tickets opened and closed every hour over a certain period</p>
        <q-toolbar class="text-center">
        <div class="xs-column">
        <q-btn class="q-mb-1" icon="event" round color="primary">
            <q-popup-proxy @before-show="updateStartDate" transition-show="scale" transition-hide="scale">
                <q-date minimal navigation-min-year-month="2019/07" v-model="startDate">
                    <div class="row items-center justify-end q-gutter-sm">
                        <q-btn label="Cancel" color="primary" flat v-close-popup />
                        <q-btn label="OK" color="primary" flat @click="save" v-close-popup />
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
        <q-select label-color="white" @input="onChangeUser()" v-model="selectedUser" use-input input-debounce="0" label="Users" @filter="filterFn" :options="users">
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
          <p>Please try with another date and/or employee</p>
        </div>
        <q-scroll-area
          v-if="empty == false"
          horizontal
          style="minHeight: 250px; height: 300px; width: 700px; max-height: 100%; max-width: 100%;"
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
  name: 'DailyTicketTracker',
  components: {
    CardBase,
  },
  data () {
    return {
      selectedUser: '',
      userRoute: '',
      users: [],
      usersId: [],
      userList: [],
      options: [],
      dateStart: '',
      startDate: '',
      endDate: '',
      currentDate: '',
      currentDateMinus: '',
      totalHours: [],
      dataUrl: 'http://192.168.8.85:8000/dashboard/hourlygrid/',
      paramRoute: '%/week/now',
      series: '',
      empty: true,
      chartOptions: {}
    }
  },
  mounted() { 
    this.currentDate = new Date()
    this.endDate = date.formatDate(this.currentDate, 'YYYY-MM-DD')
    this.currentDateMinus = date.subtractFromDate(this.currentDate, { days: 30 })
    this.startDate = date.formatDate(this.currentDateMinus, 'YYYY-MM-DD')
    this.dateStart = this.startDate
    this.paramRoute = '%/' + this.startDate + '/' + this.endDate
    this.fetchData()
  },
  methods: {
    fetchData: async function() {
        try {
          this.$axios.get(this.dataUrl + this.paramRoute , {
              headers: { 'Content-Type': 'application/json' },
              crossdomain: true
              }
              ).then(res => {
              const getUsers = res.data.users
              for (let i = 0; i < getUsers.length; i++) {
                  this.userList = getUsers
                  this.users.push(this.userList[i].name)
                  this.usersId.push(this.userList[i].id)
              }
              console.log(res.data)
              this.series = [{
                name: 'Tickets Opened',
                data: [{ x: "", y: ""}]
              }, {
                name: 'Tickets Closed',
                data: [{ x: "", y: ""}]
              }],
              console.log(this.series)
              let open = res.data.openTickets
              let closed = res.data.closedTickets
              this.options = this.users
              this.chartOptions = {
                chart: {
                  events: {
                    beforeResetZoom: function() {
                      return {
                        xaxis: {
                          min: 0,
                          max: 23,
                          tickAmount: 23,
                          type: 'numeric',
                          label: {
                            show: true,
                          }
                        }
                      } 
                    },
                    beforeZoom : (e, {xaxis}) => {
                      let maindifference = 23
                      let zoomdifference =   xaxis.max - xaxis.min
                      if( zoomdifference > maindifference )
                      return  {
                          // dont zoom out any further
                          xaxis: {
                              min: 0,
                              max: 23,
                              tickAmount: 23
                          }
                      }; 
                      else {
                          return {
                              // keep on zooming
                              xaxis: {
                                  min: xaxis.min,
                                  max: xaxis.max,
                                  // tickAmount: parseInt(xaxis.max - xaxis.min),
                                  tickAmount: 23
                              }
                          }
                      }
                  }
                  },
                    toolbar: { 
                      show: true, 
                      tools: { 
                        download: true, 
                        selection: true, 
                        zoom: true,
                        zoomin: true, 
                        zoomout: true, 
                        pan: true, 
                        reset: true },
                    },
                    type: 'bar',
                    stacked: true,
                },
                colors: ['#054206', '#AF0909'],
                animations: {
                  enabled: true,
                  easing: 'easeinout',
                  speed: 1000
                },
                grid: {
                  show: true,
                  strokeDashArray: 0,
                  padding: {
                    left: -0,
                    right: 0,
                  },
                  xaxis: {
                    lines: {
                      show: false
                    }
                  },
                  yaxis: {
                    lines: {
                      show: false
                    }
                  }
                },
                title: {
                  text: 'Hourly Ticket Tracker',
                  align: 'left',
                  style: {
                    color: '#FFF'
                  }
                },
                fill: {
                  type: 'gradient',
                  gradient: {
                    shade: 'dark',
                    type: 'vertical',
                    shadeIntensity: 0.5,
                    inverseColors: false,
                    opacityFrom: 1,
                    stops: [0, 100]
                  }
                },
                dataLabels: {
                  enabled: false,
                },
                stroke: {
                  width: 0
                },
                xaxis: {
                  type: 'numeric',
                  tickAmount: 23,
                  min: 0,
                  max: 23,
                  tooltip: {
                    formatter: function(value) {
                      if(!isNaN(value)) {
                        return parseInt(value)
                      } else {
                        return value.toFixed(0);
                      }
                    },
                  },
                  title: {
                    text: 'Hour of the day',
                    style: {
                      color: '#FFF'
                    }
                  },
                  axisBorder: {
                    show: true,
                    color: '#78909C',
                    offsetX: 10,
                    offsetY: 0
                },
                  axisTicks: {
                    show: true
                  },
                  tickPlacement: 'on',
                  labels: {
                    showDuplicates: false,
                    show: true,
                    rotateAlways: true,
                    formatter: function(value) {
                      if(value < 10) {
                        return "0" + parseInt(value) + ":00"
                      } else {
                        return parseInt(value) + ":00"
                      }
                    },
                    style: {
                      colors: '#fff'
                    }
                  },
                },
                yaxis: {
                  forceNiceScale: true,
                  type: 'numeric',
                  axisBorder: {
                    show: true,
                  },
                  tooltip: {
                    formatter: function(value) {
                      if(!isNaN(value)) {
                        return parseInt(value)
                      } else {
                        return value.toFixed(0);
                      }
                    },
                  },
                  title: {
                    text: 'Tickets',
                    style: {
                      color: '#FFF'
                    }
                  },
                  labels: {
                    showDuplicates: false,
                    style: {
                      colors: '#fff'
                    },
                    formatter: function(value) {
                      if(!isNaN(value)) {
                        return parseInt(value)
                      } else {
                        return value.toFixed(0);
                      }
                    },
                  }
                },
              }
              for (let i = 0; i < open.length; i++) {
                this.series[0].data.push({x: '', y: ''})
                this.series[0].data[i].x = open[i].hourOpened
                this.series[0].data[i].y = open[i].totalOpened
              }
              for (let i = 0; i < closed.length; i++) {
                this.series[1].data.push({x: '', y: ''})
                this.series[1].data[i].x = closed[i].hourClosed
                this.series[1].data[i].y = closed[i].totalClosed
              }
              if(open.length == 0 && closed.length == 0) {
                this.empty = true;
              } else {
                this.empty = false;
              }
              for(let i = 0; i < 24; i++) {
                this.series[0].data.push({x: i, y:0})
                this.series[1].data.push({x: i, y:0})
              }
          })
        } catch (err) {
            console.log(err)
        }
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

    updateStartDate () {
      this.startDate = this.dateStart
  },
    save () {
      this.dateStart = this.startDate
      this.dateEnd = date.addToDate(this.startDate, { days: 30 })
      this.endDate = date.formatDate(this.dateEnd, 'YYYY/MM/DD')
      try {
        if(!this.selectedUser) {
            this.userRoute = '%'
        } else {
            this.userRoute = this.selectedUser
        }
        this.paramRoute = this.userRoute + '/' + date.formatDate(this.startDate, 'YYYY-MM-DD HH:mm:ss') + '/' + date.formatDate(this.endDate, 'YYYY-MM-DD HH:mm:ss')
        this.$axios.post(this.dataUrl + this.paramRoute  , {
          headers: { 'Content-Type': 'application/json' },
          crossdomain: true
          }
          ).then(
            this.chartOptions = {
              series: [{
                name: 'Tickets Opened',
                data: [{
                  x: '',
                  y: ''
                }]
              }, {
                name: 'Tickets Closed',
                data: [{
                  x: '',
                  y: ''
                  }]
              }],
            },
            this.fetchData(this.paramRoute))
      } catch(err) {
        console.log(err)
      }
    },
    onChangeUser: async function() {
         try {
            if(!this.selectedUser) {
                this.userRoute = '%'
            } else {
                this.userRoute = this.selectedUser
            }
            this.paramRoute = this.userRoute + '/' + date.formatDate(this.startDate, 'YYYY-MM-DD HH:mm:ss') + '/' + date.formatDate(this.endDate, 'YYYY-MM-DD HH:mm:ss')
             this.$axios.post(this.dataUrl + this.paramRoute  , {
                headers: { 'Content-Type': 'application/json' },
                crossdomain: true
                }
                ).then(
                  this.chartOptions = {
                    series: [{
                      name: 'Tickets Opened',
                      data: [{
                        x: '',
                        y: ''
                      }]
                    }, {
                      name: 'Tickets Closed',
                      data: [{
                        x: '',
                        y: ''
                        }]
                    }],
                  },
                  this.fetchData(this.paramRoute))
        } catch(err) {
            console.log(err)
        }
    } 
  }
}
</script>

<style>
input[type="search"] {
    color: rgb(237, 227, 227);
}

.q-mb-none {
  align-self: center !important;
  padding: 1rem 1rem 0rem 1rem;
}

.dates {
  font-weight: 600;
}

.xs-column {
  margin: auto;
}

.q-btn .q-btn-item .non-selectable .no-outline .q-btn--standard .q-btn--round .bg-primary .text-white .q-btn--actionable .q-focusable .q-hoverable .q-btn--wrap {
  margin-bottom: 1rem !important;
}

.q-field__control-container span{
    color: rgb(237, 227, 227); 
}

.chartTitle {
    color: white;
    font-size: 1.4rem;
    text-align: center;
}

.emptyContent {
  padding: 20px;
  background-color: #f44336; /* Red */
  color: white;
  margin-bottom: 15px;
  text-align: center;
  font-size: 1.3rem;
}

.emptyContent p {
  margin: auto;
}
</style>