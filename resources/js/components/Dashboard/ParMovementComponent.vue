<template>
  <div class="small">
     <canvas ref="myChart" width="100%" height="50%"></canvas>
  </div>
</template>

<script>
import Chart from 'chart.js';

export default {
  props : ['office_id','user_id'],

  mounted() {
    this.getData();
  },

  data(){
    return {
            chart_data : {
              type: 'line',
              options: {
                title: {
                  display: true,
                  text: 'Par Movement',
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
                },


              },
              data: {
                labels: [],
                datasets: [
                  {
                    label: '181+',
                    fill:true,
                    borderColor: "#B13433",
                    backgroundColor: "rgba(177, 52, 51, 0.3)",
                    data: [0,0,0,0,0,0,0]
                  },
                  {
                    label: '91-180',
                    fill:true,
                    borderColor: "#f25287",
                    backgroundColor: "rgba(242, 82, 132, 0.4)",

                    data: [0,0,0,0,0,0,0]
                  },
                  {
                    label: '61-90',
                    fill:true,
                    borderColor: "#7868e6",
                    backgroundColor: "rgba(120, 104, 230, 0.4)",

                    data: [0,0,0,0,0,0,0]
                  },
                  {
                    label: '31-60',
                    fill:true,
                    borderColor: "#5aa897",
                    backgroundColor: "rgba(90, 168, 151, 0.4)",

                    data: [0,0,0,0,0,0,0]
                  },
                  {
                    label: '1-30',
                    fill:true,
                    borderColor: "#28b5b5",
                    backgroundColor: "rgba(40, 181,181, 0.4)",

                    data: [0,0,0,0,0,0,0]
                  },
                ]
              }
      },

      chart : null,
      movements : [],
    }
  },

  methods: {
    moneyFormat(value){
      return moneyFormat(value);
    },
    chartInit(){
      this.chart = new Chart(this.$refs.myChart, this.chart_data);
    },
    getData(){
      this.chartInit();
       axios.get(this.url)
        .then(res=>{
            this.chart_data.data.labels = res.data.par_movement.labels
            this.movements = res.data.par_movement.labels
            this.chart_data.data.datasets[0].data = res.data.par_movement.par_amount['181']
            this.chart_data.data.datasets[1].data = res.data.par_movement.par_amount['91-180'];
            this.chart_data.data.datasets[2].data = res.data.par_movement.par_amount['61-90'];
            this.chart_data.data.datasets[3].data = res.data.par_movement.par_amount['31-60'];
            this.chart_data.data.datasets[4].data = res.data.par_movement.par_amount['1-30'];
            this.chart.update();
        })
    },
  },
  computed : {
    url(){
      return '/dashboard/v1/true/'+this.office_id+'/par_movement'
    },
  }
}
</script>

 