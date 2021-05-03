<template>
  <div class="small">
     <canvas ref="myChart" width="100%" height="70%"></canvas>
  </div>
</template>

<script>
import Chart from 'chart.js';
import ChartDataLabels from 'chartjs-plugin-datalabels';

export default {
  props : ['office_id'],
  data(){
    return {
      chart : null,
      chart_option : {
        type: 'pie',
        options: {
          title: {
            display: true,
            text: 'Client Outreach',
            fontSize: 20,
            fontColor: 'white',
          },
          legend:{
            labels:{
                fontColor: "white",
                fontSize:15
            }
          },
          tooltips: {
            enabled: true
          },

        },
        data: {
          labels: ["With Loans- W/ PAR", "With Loans- W/O PAR", "Without Loans"],
          datasets: [
              {
                  label: "Clients",
                  backgroundColor: ["#f05945", "#00917c","#9fd8df"],
                  datalabels : {
                    color: 'white',
                    font: (ctx)=>{
                      return {
                          weight: 600,
                          size: 15,
                      }
                    },
                    formatter: (value, ctx)=>{
                      var p = this.percentages[ctx.dataIndex];
                      return p + '%';
                    }
                  }
              }
            ]
        },
        plugins : ChartDataLabels
      },
      errors : [],
      percentages: [],
    }
  },
  mounted() {
    this.getData()
  },
  methods : {
    chartInit(){
      this.chart = new Chart(this.$refs.myChart, this.chart_option);
    },
    getData(){
      this.chartInit();
      axios.get(this.url)
      .then(res=>{
        this.chart_option.data.datasets[0].data = [];
        this.chart_option.data.datasets[0].data.push(res.data.outreach['client_with_par'][0])
        this.chart_option.data.datasets[0].data.push(res.data.outreach['client_without_par'][0])
        this.chart_option.data.datasets[0].data.push(res.data.outreach['client_without_loan'][0])
        this.percentages = res.data.outreach.percentages;
        this.chart.update()
      })
      .catch(error=>{
        console.log(error)
        // this.errors = err.response.data.errors
      })
    }
  },

  computed : {
    url(){
      return '/dashboard/v1/true/'+this.office_id+'/client_outreach'
    }
  }
}
</script>

 