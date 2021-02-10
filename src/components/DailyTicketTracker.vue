<template>
    <div>
        <apexchart type="bar" height="250" :options="chartOptions" :series="series" />
    </div>
</template>

<script>
export default {
  name: 'DailyTicketTracker',
  data () {
    return {
      series: [{
        name: 'Open Tickets',
        data: [44, 55, 57, 56, 61, 58, 63, 60, 66, 50, 22]
      }, {
        name: 'Closed Tickets',
        data: [102, 100, 92, 87, 90, 78, 42, 20, 51, 92, 102]
      }],
      chartOptions: {
        chart: {
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
          categories: ['00:00', '01:00', '02:00', '03:00', '04:00', '05:00', '06:00', '07:00', '08:00', '09:00', '10:00'],
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
  }
}
</script>