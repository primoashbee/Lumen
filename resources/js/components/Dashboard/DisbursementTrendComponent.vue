<template>
  <div class="">
     <canvas ref="myChart" width="100%" height="18%"></canvas>
  </div>
</template>

<script>
import Chart from 'chart.js';

export default {
  props : ['office_id'],
  data(){
    return {
        chart_options : {
            type: 'bar',
            options: {
              title: {
                display: true,
                text: 'Disbursement Trend',
                fontSize: 20,
                fontColor: 'white',
              },
              legend:{
                labels:{
                    fontColor: "white",
                    fontSize:15
                }
              }

            },
            data: {          
              labels: ['20-June', '21-June', '22-June', '23-June', '24-June'],
              datasets: [
                { 
                  label:'Disbursement',
                  borderColor: "#3dba9f",
                  backgroundColor: "#3dba9f",
                  data: [50000, 150000, 100000, 45000, 400000], 
                },
                {
                  label:'Repayment (P)',
                  borderColor: "#3e95cd",
                  backgroundColor: "#3e95cd",

                  data: [60000, 250000, 65000, 48000, 320000]
                },
                {
                  label:'Repayment (I)',
                  borderColor: "#8e5ea2",
                  backgroundColor: "#8e5ea2",

                  data: [6000, 25000, 6500, 4800, 32000]
                },
              ]
            }
        }
    }
  },
  mounted() {
    // this.getData();
    this.chartInit();
  },

  methods : {
    chartInit(){
      new Chart(this.$refs.myChart, this.chart_options);
    },
    getData(){
      axios.get(this.url)
        .then(res=>{ 
            // this.chart_data.data.labels = res.data.labels
            // this.chart_data.data.datasets[0].data = res.data.expected_repayments
            // this.chart_data.data.datasets[1].data = res.data.actual_repayments
            this.chartInit()
        })
    }
  },

  computed : {
    url(){
      return '/dashboard/v1/disbursement_trend/'+this.office_id
    }
  }
}
</script>

 