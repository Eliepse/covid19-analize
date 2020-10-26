import Vue from "vue"
import GeneralComponent from "./components/general.vue"
import Chart from "chart.js"

require('./bootstrap');

window.Chart = Chart

Vue.component("general", GeneralComponent)

const vm = new Vue({
	el: "#app",
});