<template>
  <div class="">
     <canvas ref="myChart" width="100%" height="30%"></canvas>
  </div>
</template>

<script>
import Chart from 'chart.js';


export default {
  props : ['office_id'],
  data(){
    return {
      chart_options: {
        type: 'horizontalBar',
        options: {
            scales: {
                xAxes: [{
                    ticks: {
                        fontColor : 'white',
                        fontSize : 12,
                        display: true,
                        beginAtZero: true,
                        callback: function(label, index, labels) {
                          return label.toLocaleString();
                        //   return moneyFormat(label/100) + 'h';
                        }
                      },
                      scaleLabel: {
                        fontColor : 'white',
                        fontSize : 12,
                        display: true,
                        // labelString: '1h = 100'
                      },
                }],
                yAxes: [{
                      ticks: {
                        fontColor : 'white',
                        fontSize : 12,
                        display: true,
                      },
                }]
            },
            title: {
              display: true,
              text: 'Client Trend',
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
          labels: [],
          datasets: [
            { 
              label:'Resigned',
              borderColor: "#c45750",
              backgroundColor: "#c45750",
              data: [], 
            },
            {
              label:'New Loans',
              borderColor: '#29bb89',
              backgroundColor: "#29bb89",

              data: []
            },
            {
              label:'Re-loan',
              borderColor: '#51c4d3',
              backgroundColor: "#51c4d3",

              data: []
            },
          ]
        }
      },
      chart : null,
    }
  },
  mounted(){
    this.getData();
    this.chartInit();
  },
  methods : {
    chartInit(){
      this.chart = new Chart(this.$refs.myChart, this.chart_options);
    },
    getData(){
      axios.get(this.url)
        .then(res=>{
          this.chart_options.data.labels = res.data.client_trend.labels;
          this.chart_options.data.datasets[0].data = res.data.client_trend.resigned;
          this.chart_options.data.datasets[1].data = res.data.client_trend.new_loans;
          this.chart.update();
        })
    }
  },
  computed : {
    url(){
      return '/wApi/dashboard/'+this.office_id+'/client_trend'
    }
  }
}
</script>

 