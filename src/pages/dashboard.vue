<template>
  <q-page class="container" style="background: #06519C;">
      <q-header elevated class="shadow-8 full-width row wrap bg custom-color justify-center">
        <q-toolbar class="bg custom-color">
            <q-toolbar-title class="absolute-center">
            Analytics Dashboard
            </q-toolbar-title>
            <q-btn
            class="absolute-right"
            style="background: #06519C"
            @click="$q.fullscreen.toggle()"
            :icon="$q.fullscreen.isActive ? 'fas fa-compress' : 'fas fa-expand'"
            />
        </q-toolbar>
        <q-tabs
            v-model="tab"
            class="text custom-green"
        >
            <q-tab @click="toggleWeek()" v-model="week" name="weekly" label="Weekly" />
            <q-tab @click="toggleMonth()" v-model="month" name="monthly" label="Monthly" />
            <q-tab @click="toggleRawData()" v-model="rawData" name="rawData" label="Raw Data"/>
        </q-tabs>
    </q-header>
    <q-tab-panels
      transition-prev="fade"
      transition-next="fade"
      v-model="tab">
      <q-tab-panel
        name="weekly"
        v-if="week == true"
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
            <Weekly24HourGrid></Weekly24HourGrid>
          </card-base>
        </div>
        <div class="col-md-6 col-sm-12 col-xs-12">
          <card-base>
            <WeeklyCompanyGraph></WeeklyCompanyGraph>
          </card-base>
        </div>
        <div class="col-md-6 col-sm-12 col-xs-12">
          <card-base>
            <WeeklyEmployeePerformance></WeeklyEmployeePerformance>
          </card-base>
        </div>
      </q-tab-panel>
      <q-tab-panel
        name="monthly"
        v-if="month == true"
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
            <Monthly24HourGrid></Monthly24HourGrid>
          </card-base>
        </div>
        <div class="col-md-6 col-sm-12 col-xs-12">
          <card-base>
            <MonthlyCompanyGraph></MonthlyCompanyGraph>
          </card-base>
        </div>
        <div class="col-md-6 col-sm-12 col-xs-12">
          <card-base>
            <MonthlyEmployeePerformance></MonthlyEmployeePerformance>
          </card-base>
        </div>
      </q-tab-panel>
      <q-tab-panel
      name="rawData"
      v-if="rawData == true"
      >
        <RawData></RawData>
      </q-tab-panel>
    </q-tab-panels>
  </q-page>
</template>

<script>
import CardBase from 'components/CardBase'
export default {
  name: 'PageIndex',
  components: {
    CardBase,
    TicketPieChart: () => import('components/TicketPieChart'),
    Weekly24HourGrid: () => import('components/Weekly24HourGrid'),
    Monthly24HourGrid: () => import('components/Monthly24HourGrid'),
    RawData: () => import('components/RawData'),
    WeeklyCompanyGraph: () => import('components/WeeklyCompanyGraph'),
    MonthlyCompanyGraph: () => import('components/MonthlyCompanyGraph'),
    WeeklyEmployeePerformance: () => import('components/WeeklyEmployeePerformance'),
    MonthlyEmployeePerformance: () => import('components/MonthlyEmployeePerformance'),
  },
  data () {
    return {
      week: true,
      month: false,
      rawData: false,
      loading: true,
      dialog: true,
      colors: [
        'linear-gradient( 135deg, #06519C 10%, #06519C 100%)',
        'linear-gradient( 135deg, #06519C 10%, #06519C 100%)',
        'linear-gradient( 135deg, #06519C 10%, #06519C 100%)',
        'linear-gradient( 135deg, #06519C 10%, #06519C 100%)'
      ],
      tab: 'weekly'
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
  },
  methods: {
    spinner() {
      this.$q.loading.show({
      backgroundColor: 'purple-10',
      delay: 0
    })
    setTimeout(() => {
      this.$q.loading.hide()
    }, 1300)
    },
    toggleWeek() {
      this.week = true;
      this.month = false;
      this.rawData = false;
      this.spinner();
    },
    toggleMonth() {
      this.week = false;
      this.month = true;
      this.rawData = false;
      this.spinner();
    },
    toggleRawData() {
      this.week = false;
      this.month = false;
      this.rawData = true;
      this.spinner();
    }
  }
}
</script>

<style>
.custom-green {
  color: white;
}
</style>
