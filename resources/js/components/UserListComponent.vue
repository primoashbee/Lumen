<template>
<div>
    <multiselect 
        v-model="selected"
        :options="options" 
        :multiple="multiple" 
        group-values="data" 
        group-label="level" 
        :group-select="false" 
        :allow-empty="true"
        placeholder="Search" 
        track-by="name" 
        label="name"
        @input = "emitToParent"
        @search-change="asyncFind"
        :clearOnSelect="false"
        :preserveSearch="true"
    >
    <span slot="noResult">Oops! No results found.</span>
    </multiselect>
    <input type="hidden" name="office_id" :value="value.id" @change="emitToParent">
</div>
</template>

<script>
import Multiselect from 'vue-multiselect'
import { debounce } from 'lodash';


export default {
    components: {
        Multiselect
    },
    props : ['multiple'],
    created(){
        this.asyncFind = debounce(this.asyncFind.bind(this), 500);
    },
    data(){
        return {
            lists: null,
            options: [],
            selected:null,
            value: [],
        }
    },
    methods : {
        search(){

        },
        asyncFind(query){        
            axios.post('/search',{
                keyword: query,
                list: ['users']
            })
            .then(res=>{
                this.options = res.data
            })
        },
        emitToParent(val){
            this.$emit('userSelected',val)
        }
    }
}
</script>

<style scoped>
    @import "~vue-multiselect/dist/vue-multiselect.min.css";
    .multiselect__input{
        background: #ffffff !important;
    }
    .multiselect__tags{
        background: #ffffff !important;
        border-color: #ffffff !important;
    }

</style>
