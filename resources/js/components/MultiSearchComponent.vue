<template>
<div class="modal fade modal-search" id="search_bar" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
<div class="modal-dialog" role="document">
    <div class="modal-content">
    <div class="modal-header">
 
        <!-- <input type="text" class="form-control search_text" placeholder="SEARCH" name="" v-model="query">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
        </button> -->
        <multiselect 
    
        
        :options="options" 
        :multiple="false" 
        group-values="data" 
        group-label="level" 
        :group-select="false" 
        :allow-empty="false"
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
    </div>
</div>
</div>
</template>

<script>
import Multiselect from 'vue-multiselect'
import { debounce } from 'lodash';


export default {
    components: {
        Multiselect
    },
    props : ['mode'],
    created(){
        this.asyncFind = debounce(this.asyncFind.bind(this), 500);
    },
    data(){
        return {
            lists: null,
            list: ['users','clients'],
            options: [],
            selected:null,
            value: [],
        }
    },
    methods : {
        asyncFind(query){
            axios.post('/search',{
                keyword: query,
                list: this.list
            })
            .then(res=>{
                this.options = res.data
            })
        },
        emitToParent(val){
            location.href=val['link']
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
