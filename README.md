<p align="center">
  <a href="https://cli.vuejs.org/guide/installation.html"><img src="https://shields.io/badge/Framework-Vue.js-brightgreen.svg" alt="Framework"></a>
  <a href="https://quasar.dev/quasar-cli/installation"><img src="https://shields.io/badge/Styling-Quasar-brightgreen.svg" alt="Framework"></a>
  <a href="https://github.com/apexcharts/vue-apexcharts#installing-via-npm"><img src="https://shields.io/badge/Charts-ApexCharts-brightgreen.svg" alt="Framework"></a>
</p>

### About the Project

This project was built with lots of love and coffee, for Isocel Télécom Bénin, by Paul "Frenchy" Yves-Menager

Its purpose is to provide analytical information regarding the company's ticketing application

The dashboard is divided into 3 tabs : WEEKLY, MONTHLY and RAW DATA

WEEKLY & MONTHLY display all the graphs & charts, while RAW DATA displays the table


### The Graphs & Charts

<img src="https://github.com/FrenchyProds/Ticket-Analytics/blob/main/assets/readme_screenshots/graphs.png?raw=true"></img>

###### ~~~~ TICKET PIE CHART ~~~~

This chart shows the total amount of tickets that are currently open and closed ever since the application was released

###### ~~~~ 24 HOUR GRID ~~~~

This graph shows the total amount of tickets that were both opened and closed, grouped by hour

Users can filter by :
- date
- employee name

###### ~~~~ COMPANY TICKET TRACKER ~~~~

This graph shows the total amount of tickets that were both opened and closed

Users can filter by :
- date
- company name
- problem name
- detail name

###### ~~~~ EMPLOYEE PERFORMANCE ~~~~

This graph shows the total amount of tickets that were both opened and closed

Users can filter by :
- date
- employee name


###### ~~~~ RAW DATA ~~~~

<img src="https://github.com/FrenchyProds/Ticket-Analytics/blob/main/assets/readme_screenshots/rawData.png?raw=true"></img>

This table displays every single ticket that exists in the database

- Columns are sortable (ASC / DESC)
- Most columns are searchable
- The entire table is searchable
- Users can group the results by using the "GROUP BY" dropdown menu
- 10 results are shown by default, this can be modified by the user
- Every column except Ticket id can be toggled on and off by using the "Toggle Column" button
<img src="https://github.com/FrenchyProds/Ticket-Analytics/blob/main/assets/readme_screenshots/toggleColumns.png?raw=true"></img>

Users can filter by date
- The default starting date being the 19th of April 2019 (creation date of the first ticket)
- The default ending date being the current date

### The Stack

The backend was built by the company's developpment team with PHP Symfony
The frontend was built by Paul with Vue and Quasar using a separate server to the backend.

To install the backend, please contact either Fabrice or Clovis
To install the frontend inside the backend, please contact Fabrice

### Developper Documentation

### Installation procedure

###### Install the dependencies
```bash
npm install
```

###### Start the app in development mode (hot-code reloading, error reporting, etc.)
```bash
quasar dev
```

###### If you get a .babel-src error when trying to start the app, run the following command
```bash
npx quasar dev
```

###### Once the application is successfully launched, you should have localhost:8080 open automatically in your browser
To access the analytics section, navigate to http://localhost:8080/dashboard


###### Lint the files
```bash
npm run lint
```

###### Build the app for production
```bash
quasar build
```

###### Customize the configuration
See [Configuring quasar.conf.js](https://quasar.dev/quasar-cli/quasar-conf-js).


### Necessary tools to run the standalone frontend :

All tool versions can be found inside the <a href="https://github.com/FrenchyProds/Ticket-Analytics/blob/main/package.json">package.json</a> file

###### Node.js - https://nodejs.org/

The JavaScript runtime environment

###### Npm or Yarn - https://classic.yarnpkg.com/en/docs/install/#windows-stable

The package manager, up to personal preference

###### Vue.js - https://cli.vuejs.org/guide/installation.html

The JavaScript frontend framework

###### Quasar - https://quasar.dev/quasar-cli/installation

The Vue.js styling library

###### Webpack 

The JavaScript package bundler

###### ApexCharts - https://github.com/apexcharts/vue-apexcharts#installing-via-npm

The JavaScript chart / graph creation library

<hr />

### How the code structure works

All of the templating files are located inside the src folder

```
Ticket-Analytics/
├── src/
    ├── Components/
    │   └── CardBase.vue
    │   └── OtherComponents.vue
    ├── pages/
        └── dashboard.vue
```

To modify the entire dashboard, you will need to make code changes to dashboard.vue
For changes to individual graphs, go to their respective file inside the pages/ folder
<hr />

All of the date pickers are controlled by the following button + q-date combination
```vue
<template>
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
</template>
```
<hr />

All of the inputs (except for the Pie Chart) are controlled by q-select elements
```vue
<template>
    <q-select label-color="white" outlined use-input @input="onChangeUser()" v-model="selectedUser" label="Users" @filter="filterFn" :options="users">
        <template v-slot:no-option>
            <q-item>
                <q-item-section class="text-grey">
                No results
                </q-item-section>
            </q-item>
        </template>
    </q-select>
</template>
```
<hr />

Some of the charts and graphs are located inside q-scroll-areas, this is to allow overflow
```vue
<template>
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
</template>
```
<hr />

All of the data is fetched through the routes that are specified in 
```vue
<script>
data() { 
    return {
        dataUrl:'',
        paramRoute: '',
        series: '',
    }
}
</script>
```
It is then appended to the charts and graphs through the :series="series" variable

<hr />

Upon page mount, default dates are set, the routes are dynamically over-written and the first batch of data is fetched
dateLimit is used to stop the user from selecting a date that is beyond the current date.
```vue
<script>
mounted() { 
    this.currentDate = new Date()
    this.dateLimit = date.formatDate(this.currentDate, 'YYYY/MM')
    this.endDate = date.formatDate(this.currentDate, 'YYYY-MM-DD')
    this.currentDateMinus = date.subtractFromDate(this.currentDate, { days: 30 })
    this.startDate = date.formatDate(this.currentDateMinus, 'YYYY-MM-DD')
    this.paramRoute = '%/' + this.startDate + '/' + this.endDate
    this.fetchData()
  },
</script>
```
<hr />

fetchData() is the main function for our components, as it handles every single get request and sets all the content of the page
```vue
<script>
methods: {
    fetchData: async function() {
        try { 
            this.$axios.get(this.dataUrl + this.paramRoute , {
              headers: { 'Content-Type': 'application/json' },
              crossdomain: true
            }
        }
        catch(error) {

        }
    }
}
</script>
```
All of the necessary data is appended to our series, and all chartOptions are updated.

<hr />

A function is declared to control every input. Upon being fired it will post the updated data to the backend and then run fetchData() with the updated parameters. For instance the function below is the one that is called every time the date is updated through the calendars.
```vue
<script>
methods: {
    save: async function() {
        try {
        } catch (error) {
        }
    }
}
</script>
```