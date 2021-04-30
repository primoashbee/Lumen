<template>
  <div>
  <multiselect 
    
    v-model="value" 
    :options="options" 
    :multiple="allow_multiple" 
    group-values="data" 
    group-label="type" 
    :group-select="allow_group_select" 
    :allow-empty="allow_empty"
    placeholder="Select Type" 
    track-by="name" 
    label="name"
    @input = "emitToParent"
    
    >
      <span slot="noResult">Oops! No elements found. Consider changing the search query.</span>
    </multiselect>
    <input type="hidden" name="office_id" :value="value.id" @change="emitToParent">
    
  
  </div>
</template>

<script>
import Multiselect from 'vue-multiselect'

export default {
  components: {
    Multiselect
  },
  props: ['name','default_value','type','allow_empty','allow_group_select','allow_multiple'],
  created(){
      // this._allow_group_select = this.allow_group_select === undefined ? false : true
      // this._allow_multiple = this.allow_multiple === undefined ? false : true
      // this._allow_empty = this.allow_empty === undefined ? false : true
      this.getOptions(this.type);
  },
  data () {
    return {
        lists: null,
        options: [],
        value: [],
        _allow_group_select : false,
        _allow_empty : false,
        _allow_multiple : false,
    }
  },
  methods: {
    emitToParent(){
      if(this.value!=null){
        this.$emit('transactionSelected', this.value);
      }
    },

    getOptions(type){
      axios.get('/transactions?type='+type)
        .then(res=>{
            this.options = res.data

        })
    }, 
    fetchListByLevel(level){
        axios.get('/usr/branches?level='+level)
        .then(res=>{
          this.options=res.data
            if(this.default_value!==undefined){
              this.options.filter( obj => {
                var item = obj.data.filter(office => {
                   office.id == this.default_value ? this.value = office : ''
                })
              }) 
          }
        })
    }
  },

  
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

    .is-invalid .multiselect__tags {
      border: 1px solid red !important;
    }

</style>

