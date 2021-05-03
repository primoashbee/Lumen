<template>
    <datepicker @input="emitToParent" 
        v-model="date" 
        :input-class="inputClass"  
        :format="customFormatter" 
        :clear-button="true"
    >
    </datepicker>

</template>


<script>

import Datepicker from 'vuejs-datepicker';
export default {
  components: {
    Datepicker
  },
  props: ['name','id','default_value','has-error'],
  created(){
    if(this.default_value!==undefined){
        this.react()
    }
  },
  methods:{
      emitToParent(){
          this.$emit('datePicked', this.returnData)
      },
      customFormatter(){
        return moment(this.date).format('MMMM D, YYYY');
      },
      react(){
          this.date = this.default_value
      }
  },
  data(){
      return {
          date: null,
          class: 'form-control'
      }
  },
  watch: {
    //   date:{
    //       immediate: false,
    //       handler(){
    //         this.emitToParent()
    //       }
    //   }
  },
  computed : {
      inputClass(){
          return this.class+ ' '+this.hasError
      },
      returnData(){
          return this.date.toString().replace("(Philippine Standard Time)", "");
      }
  }

}

</script>

<style>
    .form-control:disabled, .form-control[readonly]{
        background-color: transparent;
    }

    .is-invalid {
        border-color: red;
    }
 
</style>