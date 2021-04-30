<template>
  <div class="small">
     <canvas ref="myChart" width="100%" height="50%"></canvas>
  </div>
</template>

<script>
import Chart from 'chart.js';

export default {
  props : ['office_id','user_id'],
  data(){
    return {
      data : [],
      labels : [],
      expected_repayments : [],
      actual_repayments : [],
      chart_data : {
              type: 'line',
              options: {
                title: {
                  display: true,
                  text: 'Repayment Trend',
                  fontSize: 20,
                  fontColor: 'white',
                },
                legend:{
                  labels:{
                      fontColor: "white",
                      fontSize:15
                  }
                },
                scales : {
                  yAxes : [{
                    ticks : {
                      fontColor : 'white',
                      fontSize : 12,
                      beginAtZero: true,
                      callback: function(label, index, labels) {
                        return moneyFormat(label/1000) + 'k';
                      },
                    },
                    scaleLabel: {
                      fontColor : 'white',
                      fontSize : 12,
                      display: true,
                      labelString: '1k = 1,000'
                    }
                  }],
                  xAxes : [{
                    ticks : {
                      fontColor: 'white',
                      fontSize:12,
                      beginAtZero: true
                    }
                  }]
                },


              },
              data: {
                labels: [],
                datasets: [
                  {
                    label: 'Expected Repayment',
                    fill:false,
                    borderColor: "#0000FF",
                    data: [0,0,0,0,0,0,0]
                  },
                  {
                    label: 'Actual Repayment',
                    fill:false,
                    borderColor: "#faa26d",
                    data: [0,0,0,0,0,0,0]
                  },
                ]
              }
      },

      chart : null,

 
    }
  },
  mounted() {
      this.getData();    
      
      window.Echo.private(this.repaymentChannel)
        .listen('.loan-payment',data =>{
          console.log('meron')
          this.paymentMade(data.data);
      })
      window.Echo.private(this.expectedChannel)
        .listen('.loan-disbursed',data =>{
          this.disbursementMade(data.data);
      })

        
  },
  methods : {
    chartInit(){
      this.chart = new Chart(this.$refs.myChart, this.chart_data);
    },
    getData(){
      this.chartInit();
       axios.get(this.url)
        .then(res=>{
            
            this.chart_data.data.labels = res.data.repayment_trend.labels
            this.chart_data.data.datasets[0].data = res.data.repayment_trend.expected_repayment
            this.chart_data.data.datasets[1].data = res.data.repayment_trend.actual_repayment
            this.chart.update();
        })

    },
    paymentMade(data){ 
      
      //get index of date
      var index = this.chart_data.data.labels.findIndex(x=>x == data.date);

      
      //actual repayment index is 1
      var curr_value = this.chart_data.data.datasets[1].data[index];
      

      var new_value = parseInt(curr_value) + parseInt(data.amount);
      
      this.chart_data.data.datasets[1].data[index] = new_value

      this.chart.update();
    },
    disbursementMade(){
      return;
      //get index of date
      var index = this.chart_data.data.labels.findIndex(x=>x == data.date);
      //actual repayment index is 1
      var curr_value = this.chart_data.data.datasets[0].data[index];

      var new_value = parseInt(curr_value) + parseInt(data.amount);
      console.log('New Value' , new_value);
      this.chart_data.data.datasets[0].data[index] = parseInt(curr_value) + parseInt(data.amount)

      this.chartInit();
    },

    
  },
  computed : {
    url(){
      return '/dashboard/v1/true/'+this.office_id+'/repayment_trend'
    },
    repaymentChannel(){
      return 'dashboard.charts.repayment.'+this.office_id
    },
    expectedChannel(){
      return 'dashboard.charts.disbursement.'+this.office_id
    }
 
  }
}
</script>

 