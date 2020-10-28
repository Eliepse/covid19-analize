<template>
  <canvas ref="canvas" width="1280" height="400"></canvas>
</template>

<script>
export default {
  name: "progression",
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
    const chart = new Chart(this.$refs.canvas, {
      type: 'bar',
      options: {
        animation: {
          duration: 0
        }
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
          },
          {
            label: 'New cases',
            data: [],
            backgroundColor: '#d0d0d0',
            barPercentage: 1,
            order: 3,
          },
        ]
      },
    });
    Axios.get("/api/progression/" + this.country)
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
