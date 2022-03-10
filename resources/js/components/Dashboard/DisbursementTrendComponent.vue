<template>
  <div>
     <canvas ref="myChart" width="100%" height="25%"></canvas>
  </div>
</template>

<script>
import Chart from 'chart.js';

export default {
  props : ['office_id', 'user_id'],
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
              },
              scales : {
                yAxes : [{
                  ticks : {
                    fontColor : 'white',
                    fontSize : 12,
                    beginAtZero: true,
                    callback: function(label, index, labels) {
                        return moneyFormat(label/1000) + 'k';
                      }
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
              }

            },
            data: {
              labels: ['1','2','3','4','5','6','7'],
              datasets: [
                { 
                  label:'Disbursement',
                  borderColor: "#3dba9f",
                  backgroundColor: "#3dba9f",
                  data: [0,0,0,0,0,0,0], 
                },
                {
                  label:'Repayment (I)',
                  borderColor: "#3e95cd",
                  backgroundColor: "#3e95cd",
                  data: [0,0,0,0,0,0,0]
                },
                {
                  label:'Repayment (P)',
                  borderColor: "#8e5ea2",
                  backgroundColor: "#8e5ea2",
                  data: [0,0,0,0,0,0,0]
                },
              ]
            }
        },
        labels : null,
        chart : null
    }
  },
  mounted() {
    this.getData();

    window.Echo.private(this.repaymentChannel)
      .listen('.loan-payment',data =>{
        this.paymentMade(data.data);
    })
    window.Echo.private('dashboard.notifications.'+this.office_id)
      .listen('.loan-disbursed',data =>{
        // console.log(data)
        this.disbursementMade(data.data);
    })
    this.chartInit();
  },

  methods : {
    chartInit(){
      this.chart  = new Chart(this.$refs.myChart, this.chart_options);
    },
    getData(){
      axios.get(this.url)
        .then(res=>{ 
          // console.log(res.data)
            this.chart_options.data.labels = res.data.disbursement_trend.labels
            this.chart_options.data.datasets[0].data = res.data.disbursement_trend.disbursements
            this.chart_options.data.datasets[1].data = res.data.disbursement_trend.repayment_interest
            this.chart_options.data.datasets[2].data = res.data.disbursement_trend.repayment_principal
            this.chart.update();
        })
    },
    paymentMade(data){ 
      //get index of date
      var index = this.chart_options.data.labels.findIndex(x=>x == data.date);

      //actual repayment interest index is 1
      var interest_curr_value = this.chart_options.data.datasets[1].data[index];
      

      var interest_new_value = parseInt(interest_curr_value) + parseInt(data.summary.interest_paid);
      
      this.chart_options.data.datasets[1].data[index] = interest_new_value

      //actual repayment principal index is 2
      var principal_curr_value = this.chart_options.data.datasets[2].data[index];
      

      var principal_new_value = parseInt(principal_curr_value) + parseInt(data.summary.principal_paid);
      
      this.chart_options.data.datasets[2].data[index] = principal_new_value

      this.chart.update();
    },

    disbursementMade(data){

      //get index of date
      var index = this.chart_options.data.labels.findIndex(x=>x == data.date);
      //actual repayment interest index is 1
      var disbursed_curr_value = this.chart_options.data.datasets[0].data[index];
      var disbursed_value = parseInt(disbursed_curr_value) + parseInt(data.amount);
      this.chart_options.data.datasets[0].data[index] = disbursed_value
      this.chart.update();
    },

  },

  computed : {
    url(){
      return '/wApi/dashboard/'+this.office_id+'/disbursement_trend'
    },
    repaymentChannel(){
      return 'dashboard.charts.repayment.'+this.office_id
    },
  }
}
</script>

 