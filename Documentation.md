### ABOUT THE PROJECT ###

This project was built for Isocel Télécom Bénin by Paul "Frenchy" Yves-Menager

Its purpose is to provide analytical information regarding the company's ticketing application

The dashboard is divided into 3 tabs : WEEKLY, MONTHLY and RAW DATA

WEEKLY & MONTHLY display all the graphs & charts, while RAW DATA displays the table


### THE GRAPHS AND CHARTS ###

# ~~~~ TICKET PIE CHART ~~~~ #

This chart shows the total amount of tickets that are currently open and closed ever since the application was released

# ~~~~ 24 HOUR GRID ~~~~ #

This graph shows the total amount of tickets that were both opened and closed, grouped by hour

Users can filter by :
- date
- employee name

# ~~~~ COMPANY TICKET TRACKER ~~~~ #

This graph shows the total amount of tickets that were both opened and closed

Users can filter by :
- date
- company name
- problem name
- detail name

# ~~~~ EMPLOYEE PERFORMANCE ~~~~ #

This graph shows the total amount of tickets that were both opened and closed

Users can filter by :
- date
- employee name


# ~~~~ RAW DATA ~~~~ #

This table displays every single ticket that exists in the database

- Columns are sortable (ASC / DESC)
- Most columns are searchable
- Every column except Ticket id can be toggled on and off by using the "Toggle Column" button
- The entire table is searchable
- Users can group the results by using the "GROUP BY" dropdown menu
- 10 results are shown by default, this can be modified by the user

Users can filter by date
- The default starting date being the 19th of April 2019 (creation date of the first ticket)
- The default ending date being the current date

### THE STACK ###

The backend was built by the company's developpment team with PHP Symfony
The frontend was built by Paul with Vue and Quasar using a separate server to the backend.

To install the backend, please contact either Fabrice or Clovis
To install the frontend inside the backend, please contact Fabrice
To install the frontend as a standalone project, without any backend, please follow the README.md documentation


### DEVELOPER DOCUMENTATION ###


# Necessary tools to run the standalone frontend : #

ALL TOOL VERSIONS CAN BE FOUND IN THE PACKAGE.JSON FILE, LOCATED IN THE ROOT FOLDER

# Node.js - https://nodejs.org/

The JavaScript runtime environment

# Npm or Yarn - https://classic.yarnpkg.com/en/docs/install/#windows-stable

The package manager, up to personal preference

# Vue.js - https://cli.vuejs.org/guide/installation.html

The JavaScript frontend framework

# Quasar - https://quasar.dev/quasar-cli/installation

The Vue.js styling library

# Webpack 

The JavaScript package bundler

# ApexCharts - https://github.com/apexcharts/vue-apexcharts#installing-via-npm

The JavaScript chart / graph creation library