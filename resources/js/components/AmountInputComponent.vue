<template>
  <div>
    <input type="text"  :class="lastClass"  v-model="value" @keypress="isNumber($event)" @paste="paste($event)" :disabled="readonly" v-debounce:200ms="emitToParent" :tabindex="tabindex">
  </div>
</template>

<script>
import vueDebounce from 'vue-debounce'

Vue.use(vueDebounce, {
  listenTo: 'input'
})

export default {

  props: ['account_info','readonly','amount','tabindex','add_class'],
  data () {
    return {
        value: null,
        input_class:"form-control"
    }
  },
  created(){
    
      if(this.amount==null){
          this.value = null
      }
      if(this.readonly){
        
      }
    
  },
  methods: {
    emitToParent(){
      // if(this.amountPasses){
        this.account_info.amount = parseFloat(this.value)
        this.$emit('amountEncoded', this.account_info)
      // }
    },
    paste(evt){
      var amount = evt.clipboardData.getData('text').replace(',','');
      evt.target.blur();
      if(isNaN(amount)){
        return false;
      }
      this.value = amount
      this.emitToParent()
      return true;
    },
    isNumber(evt) {
      
      evt = (evt) ? evt : window.event;
      var charCode = (evt.which) ? evt.which : evt.keyCode;
      if ((charCode > 31 && (charCode < 48 || charCode > 57)) && charCode !== 46) {
        evt.preventDefault();
      } else {
        
        if(!this.amountPasses){
          evt.preventDefault()
        }
      }
    }
  },
  computed : {
    lastClass(){
      if(this.readonly){
        return 'form-control readonly ' + this.add_class 
      }
      return 'form-control ' + this.add_class
    },
    amountPasses(){
      return !isNaN(this.value) ? true : false;
    }
  }
}

</script>
<style>
    @import "~vue-multiselect/dist/vue-multiselect.min.css";
    .multiselect__tags{
      background: #27293d;
    }
    .multiselect__input{
      background: #27293d!important;
      border-color:#2b3553
    }
    .multiselect__single{
      background: #27293d!important;
      color: white;
    }

    .readonly{
      background-color:gray !important;
    }
</style>

