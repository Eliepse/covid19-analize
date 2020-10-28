import Vue from "vue"
import GeneralComponent from "./components/general.vue"
import ProgressionComponent from "./components/progression.vue"
import Chart from "chart.js"

require('./bootstrap');

window.Chart = Chart

Vue.component("general", GeneralComponent)
Vue.component("progression", ProgressionComponent)

const vm = new Vue({
	el: "#app",
});
