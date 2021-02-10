<template>
  <q-page class="container" style="background: #343E59;">
      <q-header elevated class="shadow-8 full-width row wrap bg-custom-blue-dark justify-center">
        <q-toolbar class="bg-custom-blue-dark">
            <q-toolbar-title class="absolute-center">
            Analytics Dashboard
            </q-toolbar-title>
            <q-btn
            class="absolute-right"
            style="background: #36384c"
            @click="$q.fullscreen.toggle()"
            :icon="$q.fullscreen.isActive ? 'fas fa-compress' : 'fas fa-expand'"
            />
        </q-toolbar>
        <q-tabs
            v-model="tab"
            class="text-teal "
        >
            <q-tab name="weekly" label="Weekly" />
            <q-tab name="monthly" label="Monthly" />
        </q-tabs>
    </q-header>
    <div
      class="row q-col-gutter-md q-px-md q-py-md"
      key="allCharts"
    >
      <div class="col-md-6 col-sm-12 col-xs-12">
        <card-base>
          <TicketPieChart></TicketPieChart>
        </card-base>
      </div>
      <div class="col-md-6 col-sm-12 col-xs-12">
        <card-base>
          <DailyTicketTracker></DailyTicketTracker>
        </card-base>
      </div>
      <!--  <div class="col-md-6 col-sm-12 col-xs-12">
        <card-base>
          <apex-donut></apex-donut>
        </card-base>
      </div>
      <div class="col-md-6 col-sm-12 col-xs-12">
        <card-base>
          <apex-line></apex-line>
        </card-base>
      </div>
      <div class="col-md-6 col-sm-12 col-xs-12">
        <card-base>
          <apex-line-gradient></apex-line-gradient>
        </card-base>
      </div>
      <div class="col-md-6 col-sm-12 col-xs-12">
        <card-base>
          <apex-multiple-radial-bars></apex-multiple-radial-bars>
        </card-base>
      </div>
      <div class="col-md-6 col-sm-12 col-xs-12">
        <card-base>
          <apex-heatmap></apex-heatmap>
        </card-base>
      </div>
      <div class="col-md-6 col-sm-12 col-xs-12">
        <card-base>
          <apex-radial-bar></apex-radial-bar>
        </card-base>
      </div>
      <div class="col-md-6 col-sm-12 col-xs-12">
        <card-base>
          <apex-bubble></apex-bubble>
        </card-base>
      </div>
      <div class="col-md-6 col-sm-12 col-xs-12">
        <card-base>
          <apex-radar></apex-radar>
        </card-base>
      </div>
      <div class="col-md-6 col-sm-12 col-xs-12">
        <card-base>
          <apex-polar-map></apex-polar-map>
        </card-base>
      </div>
      <div class="col-md-6 col-sm-12 col-xs-12">
        <card-base>
          <apex-candle-stick></apex-candle-stick>
        </card-base>
      </div>
      <div class="col-md-6 col-sm-12 col-xs-12">
        <card-base>
          <apex-treemap></apex-treemap>
        </card-base>
      </div>
      <div class="col-md-6 col-sm-12 col-xs-12">
        <card-base>
          <apex-line-column></apex-line-column>
        </card-base>
      </div>
      <div class="col-md-6 col-sm-12 col-xs-12">
        <apex-line-column-with-scroll></apex-line-column-with-scroll>
      </div>
      <div class="col-md-6 col-sm-12 col-xs-12">
        <card-base>
          <apex-line-scatter></apex-line-scatter>
        </card-base>
      </div>
      <div class="col-md-6 col-sm-12 col-xs-12">
        <card-base>
          <apex-area></apex-area>
        </card-base>
      </div>
      <div class="col-md-6 col-sm-12 col-xs-12">
        <card-base>
          <apex-bar-charts-grouped></apex-bar-charts-grouped>
        </card-base>
      </div>
      <div class="col-md-6 col-sm-12 col-xs-12">
        <card-base>
          <apex-column-charts-basic></apex-column-charts-basic>
        </card-base>
      </div>
      <div class="col-md-6 col-sm-12 col-xs-12">
        <card-base>
          <apex-column-with-data-labels></apex-column-with-data-labels>
        </card-base>
      </div>
      <div class="col-md-6 col-sm-12 col-xs-12">
        <card-base>
          <apex-line-with-data-labels></apex-line-with-data-labels>
        </card-base>
      </div>
      <div class="col-md-6 col-sm-12 col-xs-12">
        <card-base>
          <apex-multiple-yaxis></apex-multiple-yaxis>
        </card-base>
      </div>
      <div class="col-md-6 col-sm-12 col-xs-12">
        <card-base>
          <apex-simple-pie-chart></apex-simple-pie-chart>
        </card-base>
      </div>
      <div class="col-md-6 col-sm-12 col-xs-12">
        <card-base>
          <apex-stacked-columns-100></apex-stacked-columns-100>
        </card-base>
      </div> -->
    </div>
  </q-page>
</template>

<script>
import CardBase from 'components/CardBase'
export default {
  name: 'PageIndex',
  components: {
    CardBase,
    TicketPieChart: () => import('components/TicketPieChart'),
    DailyTicketTracker: () => import('components/DailyTicketTracker'),
  },
  data () {
    return {
      loading: true,
      dialog: true,
      colors: [
        'linear-gradient( 135deg, #ABDCFF 10%, #0396FF 100%)',
        'linear-gradient( 135deg, #2AFADF 10%, #4C83FF 100%)',
        'linear-gradient( 135deg, #FFD3A5 10%, #FD6585 100%)',
        'linear-gradient( 135deg, #EE9AE5 10%, #5961F9 100%)'
      ],
      tab: 'mails'
    }
  },
  created () {
     this.$q.loading.show({
      backgroundColor: 'purple-10',
      delay: 0
    })
    setTimeout(() => {
      this.$q.loading.hide()
    }, 1300)
  }
}
</script>
