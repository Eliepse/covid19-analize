<template>
  <canvas ref="canvas" width="1280" height="400"></canvas>
</template>

<script>
export default {
  name: "general",
  props: {
    country: {
      type: String,
      required: false
    }
  },
  data: function () {
    return {}
  },
  mounted() {
    Chart.defaults.bar.barPercentage = 1.0
    Chart.defaults.bar.categoryPercentage = 1.0
    const chart = new Chart(this.$refs.canvas, {
      type: 'bar',
      options: {
        animation: {
          duration: 0
        },
        tooltips: {
            mode: 'x'
        },
      },
      data: {
        datasets: [
          {
            label: 'average 2',
            data: [],
            borderColor: '#f000f0',
            backgroundColor: 'rgba(0,0,0,0)',
            fill: false,
            type: 'line',
            order: 1,
            pointRadius: 0,
            pointHitRadius: 3,
          },
          {
            label: 'average 1',
            data: [],
            borderColor: '#00b0b0',
            backgroundColor: 'rgba(0,0,0,0)',
            fill: false,
            type: 'line',
            order: 2,
            pointRadius: 0,
            pointHitRadius: 3,
          },
          {
            label: 'New cases',
            data: [],
            backgroundColor: '#d0d0d0',
            barThickness: 'flex',
            barPercentage: 1.0,
            categoryPercentage: 1.0,
            order: 3,
          },
        ]
      },
    });
    Axios.get("/api/general/" + this.country)
        .then((resp) => {
          chart.data.labels = Object.keys(resp.data.data.raw);
          chart.data.datasets[2].data = Object.values(resp.data.data.raw);
          chart.data.datasets[1].data = Object.values(resp.data.data.smooth);
          chart.data.datasets[0].data = Object.values(resp.data.data.smoother);
          chart.update();
        })
  }
}
</script>

<style scoped>

</style>
