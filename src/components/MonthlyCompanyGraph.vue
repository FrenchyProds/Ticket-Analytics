<template>
    <div>
        <p class="chartTitle text-primary">Company Ticket Tracker - Monthly</p>
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
                <p class="text-primary q-mb-none"><span class="dates">Start Date</span> : {{ startDate }}</p>
                
                <p class="text-primary q-mb-none"><span class="dates">End Date</span> : {{ endDate }}</p>
            </div>
            </div>
            </q-toolbar>
            <q-select label-color="primary" use-input @input="onChangeCompany()" outlined v-model="selectedCompany" label="Companies" @filter="filterFn" :options="companies">
                <template v-slot:no-option>
                    <q-item>
                        <q-item-section class="text-grey">
                        No results
                        </q-item-section>
                    </q-item>
                </template>
            </q-select>
            <q-select  q-select label-color="primary" @input="onChangeProblem()" outlined v-model="selectedProblem" label="Problems" @filter="filterFn" :options="problems">
                <template v-slot:no-option>
                    <q-item>
                        <q-item-section class="text-grey">
                        No results
                        </q-item-section>
                    </q-item>
                </template>
            </q-select>
            <q-select q-select label-color="primary" @input="onChangeDetail()" outlined v-model="selectedDetail" label="Details" @filter="filterFn" :options="details">
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
                <p>Please try with another date and/or company</p>
            </div>
            <q-scroll-area
            v-if="empty == false"
            horizontal
            style="minHeight: 250px; height: 300px; width: 2000px; max-height: 100%; max-width: 100%;"
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
    name: 'WeeklyCompanyGraph',
    components: {
        CardBase,
    },
    data () {
        return {
            selectedCompany: '',
            companyRoute: '',
            selectedProblem: '',
            problemRoute: '',
            selectedDetail: '',
            detailRoute: '',
            companies: [],
            problems: [],
            details: [],
            options: [],
            startDate: '',
            endDate: '',
            currentDate: '',
            currentDateMinusMonth: '',
            dateLimit: '',
            dataUrl: 'http://192.168.8.85:8000/dashboard/companygraph/',
            paramRoute: '%/%/%/%/%',
            series: '',
            empty: true,
            loading: true,
            chartOptions: {}
        }
    },
    mounted() {
        this.loading = false
        this.currentDate = new Date()
        this.dateLimit = date.formatDate(this.currentDate, 'YYYY/MM')
        this.endDate = date.formatDate(this.currentDate, 'YYYY-MM-DD')
        this.currentDateMinusMonth = date.subtractFromDate(this.currentDate, { days: 30 })
        this.startDate = date.formatDate(this.currentDateMinusMonth, 'YYYY-MM-DD')
        this.paramRoute = '%/%/%/' + this.startDate + '/' + this.endDate
        this.fetchData()
        this.save()
    },
    methods: {
        fetchData: async function() {
            try {
                this.$axios.get(this.dataUrl + this.paramRoute , {
                    headers: { 'Content-Type': 'application/json' },
                    crossdomain: true
                }).then(res => {
                    const getCompanies = res.data.companyList
                    if(this.companies.length === 0) {
                        for (let i = 0; i < getCompanies.length; i++) {
                            this.companies.push(getCompanies[i].companyName)
                        }
                    }
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
                        },
                        plotOptions: {
                            bar: {
                                horizontal: false,
                                columnWidth: '75%',
                            }
                        },
                        colors: ['#42A62A', '#06519C'],
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
                            text: 'Monthly Company Tracker',
                            align: 'left',
                            style: {
                                color: '#06519C'
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
                            tickAmount: 31,
                            title: {
                                text: 'Date',
                                style: {
                                color: '#06519C'
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
                                // rotateAlways: true,
                                // format: 'dd/MM',
                                formatter: function(val, timestamp) {
                                    return date.formatDate(timestamp, 'DD/MM');
                                },
                                style: {
                                colors: '#06519C'
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
                                color: '#06519C'
                                }
                            },
                            labels: {
                                showDuplicates: false,
                                style: {
                                colors: '#06519C'
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
                    this.series = [{
                        name: 'Tickets Opened',
                        data: [{ x: "", y: ""}]
                    }, {
                        name: 'Tickets Closed',
                        data: [{ x: "", y: ""}]
                    }]
                    this.companies.splice(0, this.companies.length)
                    this.problems = []
                    this.details = []
                    for (let i = 0; i < open.length; i++) {
                        this.series[0].data.push({x: '', y: ''})
                        this.series[0].data[i].x = open[i].dateOpened + ' GMT'
                        this.series[0].data[i].y = open[i].totalOpened
                        if (this.companies.indexOf(open[i].companyName) == -1 ) {
                            this.companies.push(open[i].companyName)
                        }
                        if (this.problems.indexOf(open[i].problemName) == -1 ) {
                            this.problems.push(open[i].problemName)
                        }
                        if (this.details.indexOf(open[i].detailName) == -1 ) {
                            this.details.push(open[i].detailName)
                        } 
                    }
                    for (let i = 0; i < closed.length; i++) {
                        this.series[1].data.push({x: '', y: ''})
                        this.series[1].data[i].x = closed[i].dateClosed + ' GMT'
                        this.series[1].data[i].y = closed[i].totalClosed
                        if (this.companies.indexOf(closed[i].companyName) == -1 ) {
                            this.companies.push(closed[i].companyName)
                        }
                        if (this.problems.indexOf(closed[i].problemName) == -1 ) {
                            this.problems.push(closed[i].problemName)
                        }
                        if (this.details.indexOf(closed[i].detailName) == -1 ) {
                            this.details.push(closed[i].detailName)
                        }   
                    }
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
                if(!this.selectedCompany) {
                    this.companyRoute = '%'
                } else {
                    this.companyRoute = this.selectedCompany
                }
                if(!this.selectedProblem) {
                    this.problemRoute = '%'
                } else {
                    this.problemRoute = this.selectedProblem
                }
                if(!this.selectedDetail) {
                    this.detailRoute = '%'
                } else {
                    this.detailRoute = this.selectedDetail
                }
                this.paramRoute = this.problemRoute + '/' + this.detailRoute + '/' + this.companyRoute + '/' + date.formatDate(this.startDate, 'YYYY-MM-DD') + '/' + date.formatDate(this.endDate, 'YYYY-MM-DD')
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
                                colors: '#06519C'
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

        onChangeCompany() {
             try {
                if(!this.selectedCompany) {
                    this.companyRoute = '%'
                } else {
                    this.companyRoute = this.selectedCompany
                }
                if(!this.selectedProblem) {
                    this.problemRoute = '%'
                } else {
                    this.problemRoute = this.selectedProblem
                }
                if(!this.selectedDetail) {
                    this.detailRoute = '%'
                } else {
                    this.detailRoute = this.selectedDetail
                }
                this.paramRoute = this.problemRoute + '/' + this.detailRoute + '/' + this.companyRoute + '/' + date.formatDate(this.startDate, 'YYYY-MM-DD') + '/' + date.formatDate(this.endDate, 'YYYY-MM-DD')
                 this.$axios.post(this.dataUrl + this.paramRoute , {
                    headers: { 'Content-Type': 'application/json' },
                    crossdomain: true
                }).then(
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
                                colors: '#06519C'
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
             } catch (error) {
                 console.log(error)
             }
        },

        onChangeProblem() {
            try {
                if(!this.selectedCompany) {
                    this.companyRoute = '%'
                } else {
                    this.companyRoute = this.selectedCompany
                }
                if(!this.selectedProblem) {
                    this.problemRoute = '%'
                } else {
                    this.problemRoute = this.selectedProblem
                }
                if(!this.selectedDetail) {
                    this.detailRoute = '%'
                } else {
                    this.detailRoute = this.selectedDetail
                }
                this.paramRoute = this.problemRoute + '/' + this.detailRoute + '/' + this.companyRoute + '/' + date.formatDate(this.startDate, 'YYYY-MM-DD') + '/' + date.formatDate(this.endDate, 'YYYY-MM-DD')
                this.$axios.post(this.dataUrl + this.paramRoute , {
                    headers: { 'Content-Type': 'application/json' },
                    crossdomain: true
                }).then(
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
                                colors: '#06519C'
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
             } catch (error) {
                 console.log(error)
             }
        },

        onChangeDetail() {
            try {
                if(!this.selectedCompany) {
                    this.companyRoute = '%'
                } else {
                    this.companyRoute = this.selectedCompany
                }
                if(!this.selectedProblem) {
                    this.problemRoute = '%'
                } else {
                    this.problemRoute = this.selectedProblem
                }
                if(!this.selectedDetail) {
                    this.detailRoute = '%'
                } else {
                    this.detailRoute = this.selectedDetail
                }
                this.paramRoute = this.problemRoute + '/' + this.detailRoute + '/' + this.companyRoute + '/' + date.formatDate(this.startDate, 'YYYY-MM-DD') + '/' + date.formatDate(this.endDate, 'YYYY-MM-DD')
                this.$axios.post(this.dataUrl + this.paramRoute , {
                    headers: { 'Content-Type': 'application/json' },
                    crossdomain: true
                }).then(
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
                                colors: '#06519C'
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
             } catch (error) {
                 console.log(error)
             }
        }
    }
}
</script>
