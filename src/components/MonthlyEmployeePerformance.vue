<template>
    <div>
      <p class="chartTitle">Employee Performance - Monthly</p>
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
          <p class="text-white q-mb-none"><span class="dates">Start Date</span> : {{ startDate }}</p>
          
          <p class="text-white q-mb-none"><span class="dates">End Date</span> : {{ endDate }}</p>
        </div>
        </div>
        </q-toolbar>
        <q-select label-color="white" outlined use-input @input="onChangeUser()" v-model="selectedUser" label="Users" @filter="filterFn" :options="users">
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
  name: 'Monthly24HourGrid',
  components: {
    CardBase,
  },
  data () {
    return {
      selectedUser: '',
      userRoute: '',
      users: [],
      userList: '',
      options: [],
      dateStart: '',
      startDate: '',
      endDate: '',
      currentDate: '',
      currentDateMinus: '',
      dateLimit: '',
      dataUrl: 'http://192.168.8.85:8000/dashboard/employeeperformancetracker/',
      paramRoute: '%/%/%',
      series: '',
      empty: true,
      chartOptions: {}
    }
  },
  mounted() { 
    this.currentDate = new Date()
    this.dateLimit = date.formatDate(this.currentDate, 'YYYY/MM')
    this.endDate = date.formatDate(this.currentDate, 'YYYY-MM-DD')
    this.currentDateMinus = date.subtractFromDate(this.currentDate, { days: 30 })
    this.startDate = date.formatDate(this.currentDateMinus, 'YYYY-MM-DD')
    this.dateStart = this.startDate
    this.paramRoute = '%/' + this.startDate + '/' + this.endDate
    this.fetchData()
    this.save()
  },
  methods: {
      fetchData() {
          try {
              this.$axios.get(this.dataUrl + this.paramRoute , {
                    headers: { 'Content-Type': 'application/json' },
                    crossdomain: true
                }).then(res => {
                    this.options = this.companies
                    this.chartOptions = {
                        chart: {
                            zoom: {
                                autoScaleYaxis: true
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
                                    reset: true
                                },
                            },
                            type: 'bar',
                            stacked: false,
                            horizontal: false,
                        },
                        colors: ['#42A62A', '#f44336'],
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 1000
                        },
                        grid: {
                            show: true,
                            strokeDashArray: 0,
                            padding: {
                                right: 0,
                            },
                            xaxis: {
                                label: {
                                    show: true,
                                },
                            },
                            yaxis: {
                                lines: {
                                    show: false
                                }
                            }
                        },
                        title: {
                            text: 'Monthly Employee Performance',
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
                            type: 'datetime',
                            title: {
                                text: 'Employee Name',
                                style: {
                                color: '#FFF'
                                }
                            },
                            axisBorder: {
                                show: true,
                                color: '#78909C',
                                offsetX: 0,
                                offsetY: 0
                            },
                            axisTicks: {
                                show: true
                            },
                            labels: {
                                showDuplicates: false,
                                show: true,
                                rotateAlways: true,
                                format: 'dd/MM',
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
                    
                    let open = res.data.openTickets
                    let closed = res.data.closedTickets
                    console.log(res.data)
                    this.series = [{
                        name: 'Tickets Opened',
                        data: [{ x: "", y: ""}]
                    }, {
                        name: 'Tickets Closed',
                        data: [{ x: "", y: ""}]
                    }]
                    for (let i = 0; i < open.length; i++) {
                        this.series[0].data.push({x: '', y: ''})
                        this.series[0].data[i].x = open[i].dateCreated  + ' GMT'
                        this.series[0].data[i].y = open[i].totalCreated
                        if (this.users.indexOf(open[i].employeeName) == -1 ) {
                            this.users.push(open[i].employeeName)
                        }
                    }
                    for (let i = 0; i < closed.length; i++) {
                        this.series[1].data.push({x: '', y: ''})
                        this.series[1].data[i].x = closed[i].dateClosed  + ' GMT'
                        this.series[1].data[i].y = closed[i].totalClosed
                        if (this.users.indexOf(closed[i].employeeName) == -1 ) {
                            this.users.push(closed[i].employeeName)
                        }
                    }
                    this.userList = (this.users)
                    if(open.length == 0 && closed.length == 0) {
                        this.empty = true;
                    } else {
                        this.empty = false;
                    }
                    for(let i = 0; i < 32; i++) {
                        let dateCalc = date.subtractFromDate(this.endDate, { days: i })
                        this.series[0].data.push({x: dateCalc + ' GMT', y:0})
                        this.series[1].data.push({x: dateCalc + ' GMT', y:0})
                    }
                })
                console.log(this.series)
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
            this.startDate = this.startDate
        },

        save() {
            this.endDate = date.addToDate(this.startDate, { days: 30 })
            this.endDate = date.formatDate(this.endDate, 'YYYY/MM/DD')
            try {
                if(!this.selectedUser) {
                    this.userRoute = '%'
                } else {
                    this.userRoute = this.selectedUser
                }
                this.paramRoute = this.userRoute + '/' + date.formatDate(this.startDate, 'YYYY-MM-DD') + '/' + date.formatDate(this.endDate, 'YYYY-MM-DD')
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
                        yaxis: {
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
                        }
                    },
                    this.fetchData(this.paramRoute))
            } catch(err) {
                console.log(err)
            }
        },

      onChangeUser() {
          try {
                if(!this.selectedUser) {
                    this.userRoute = '%'
                } else {
                    this.userRoute = this.selectedUser
                }
                this.paramRoute = this.userRoute + '/' + date.formatDate(this.startDate, 'YYYY-MM-DD') + '/' + date.formatDate(this.endDate, 'YYYY-MM-DD')
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
                        yaxis: {
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
                        }
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

</style>