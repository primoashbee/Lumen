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
                      beginAtZero: true
                    }
                  }],
                  xAxes : [{
                    ticks : {
                      fontColor: 'white',
                      fontSize:12,
                      beginAtZero: true
                    }
                  }]
                }
              },
              data: {
                labels: [],
                datasets: [
                  {
                    label: 'Expected Repayment',
                    fill:false,
                    borderColor: "#0000FF",
                    data: []
                  },
                  {
                    label: 'Actual Repayment',
                    fill:false,
                    borderColor: "#faa26d",

                    data: []
                  },
                ]
              }
      }

 
    }
  },
  mounted() {
      this.getData();    
      //repayment
      window.Echo.private(this.repaymentChannel)
        .listen('.loan-payment',data =>{
          console.log('lp',data);
          this.paymentMade(data.data);
      })
      window.Echo.private(this.expectedChannel)
        .listen('.loan-disbursed',data =>{
          console.log('ld,',data);
          this.disbursementMade(data.data);
      })

        
  },
  methods : {
    chartInit(){
    
      new Chart(this.$refs.myChart, this.chart_data);
    },
    getData(){
       axios.get(this.url)
        .then(res=>{
            
            this.chart_data.data.labels = res.data.labels
            this.chart_data.data.datasets[0].data = res.data.expected_repayment
            this.chart_data.data.datasets[1].data = res.data.actual_repayment
            
            this.chartInit()
        })

    },
    paymentMade(data){ 
      
      //get index of date
      var index = this.chart_data.data.labels.findIndex(x=>x == data.date);

      
      //actual repayment index is 1
      var curr_value = this.chart_data.data.datasets[1].data[index];
      console.log('New Value' , curr_value);

      var new_value = parseInt(curr_value) + parseInt(data.amount);
      console.log('New Value' , new_value);
      this.chart_data.data.datasets[1].data[index] = parseInt(curr_value) + parseInt(data.amount)

      this.chartInit();
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
    updateChart(){
          this.chart_data = {
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
                }
              },
              data: {
                labels: ["2021-03-01", "2021-03-02", "2021-03-03", "2021-03-04", "2021-03-05", "2021-03-08"],
                datasets: [
                  {
                    label: 'Expected Repayment',
                    fill:false,
                    borderColor: "#0000FF",

                    data:[6000,5000,4000,3000,2000,1000]
                  },
                  {
                    label: 'Actual Repayment',
                    fill:false,
                    borderColor: "#faa26d",

                    // data: res.data.actual_repayments
                    data: [1000,2000,3000,4000,5000,6000]
                  },
                ]
              }
          }

          this.chartInit()
    }
    
  },
  computed : {
    url(){
      return '/dashboard/v1/repayment_trend/'+this.office_id
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

 