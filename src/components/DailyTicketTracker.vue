<template>
    <div>
        <q-toolbar class="text-center">
        <div class="col row center">
        <q-btn icon="event" round color="primary">
            <q-popup-proxy @before-show="updateStartDate" transition-show="scale" transition-hide="scale">
                <q-date navigation-min-year-month="2019/07" v-model="startDate">
                    <div class="row items-center justify-end q-gutter-sm">
                        <q-btn label="Cancel" color="primary" flat v-close-popup />
                        <q-btn label="OK" color="primary" flat @click="save" v-close-popup />
                    </div>
                </q-date>
            </q-popup-proxy>
        </q-btn>
        <p class="text-white">Start Date : {{ startDate }}</p>
        </div>
        <div class="col">
        <p class="text-white">End Date : {{ endDate }}</p>
        </div>
        </q-toolbar>
        <q-select label-color="white" v-model="model" :value="users" use-input input-debounce="0" label="Users" @filter="filterFn" :options="users">
            <template v-slot:no-option>
                <q-item>
                    <q-item-section class="text-grey">
                    No results
                    </q-item-section>
                </q-item>
            </template>
        </q-select>
        <apexchart type="bar" height="250" :options="chartOptions" :series="series" />
    </div>
</template>

<script>
import { date } from 'quasar'
export default {
  name: 'DailyTicketTracker',
  data () {
    return {
      model: null,
      users: [],
      userList: [],
      options: [],
      dateStart: '',
      startDate: '',
      endDate: '',
      currentDate: '',
      currentDateMinusWeek: '',
      dataUrl: 'http://192.168.8.85:8000/dashboard/hourlygrid/',
      series: [{
        name: 'Tickets Opened',
        data: [44, 55, 57, 56, 61, 58, 63, 60, 66, 50, 22, 44, 55, 57, 56, 61, 58, 63, 60, 66, 50, 22, 50, 0]
      }, {
        name: 'Tickets Closed',
        data: [102, 100, 92, 87, 90, 78, 42, 20, 51, 92, 102, 102, 100, 92, 87, 90, 78, 42, 20, 51, 92, 102, 20, 50]
      }],
      chartOptions: {
        chart: {
            type: 'bar',
            stacked: true,
            zoom: {
                enabled: true,
                type: 'x',  
                autoScaleYaxis: false,  
                zoomedArea: {
                    fill: {
                    color: '#90CAF9',
                    opacity: 0.4
                    },
                    stroke: {
                    color: '#0D47A1',
                    opacity: 0.4,
                    width: 1
                    }
                }
            }
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
          xaxis: {
            lines: {
              show: true
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
          enabled: false
        },
        stroke: {
          width: 0
        },
        xaxis: {
          tickamount: 8,
          categories: ['00:00', '01:00', '02:00', '03:00', '04:00', '05:00', '06:00', '07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00', '22:00', '23:00'],
          labels: {
            style: {
              colors: '#fff'
            }
          }
        },
        yaxis: {
          title: {
            text: 'Tickets',
            style: {
              color: '#FFF'
            }
          },
          labels: {
            style: {
              colors: '#fff'
            }
          }
        },
        tooltip: {
        }
      }
    }
  },
  mounted() { 
      this.currentDate = new Date()
      this.endDate = date.formatDate(this.currentDate, 'YYYY-MM-DD HH:mm:ss')
      this.currentDateMinusWeek = date.subtractFromDate(this.currentDate, { days: 7 })
      this.startDate = date.formatDate(this.currentDateMinusWeek, 'YYYY-MM-DD HH:mm:ss')
      this.dateStart = this.startDate
      this.fetchData()
  },
  methods: {
      fetchData: async function() {
          try {
            this.$axios.get(this.dataUrl + '%/week/now'  , {
                headers: { 'Content-Type': 'application/json' },
                crossdomain: true
                }
                ).then(res => {
                const getUsers = res.data.users
                console.log(getUsers.length)
                for (let i = 0; i < getUsers.length; i++) {
                    this.userList = getUsers
                    this.users.push(this.userList[i].name)
                }
                this.options = this.users
                console.log(this.users)
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
      updateEndDate () {
    },
     save () {
      this.dateStart = this.startDate
      this.dateEnd = date.addToDate(this.startDate, { days: 7 })
      this.endDate = this.dateEnd
      this.endDate = date.formatDate(this.dateEnd, 'YYYY/MM/DD')
      
    }
  }
}
</script>

<style>
input[type="search"] {
    color: rgb(237, 227, 227);
}

.q-field__control-container span{
    color: rgb(237, 227, 227); 
}
</style>