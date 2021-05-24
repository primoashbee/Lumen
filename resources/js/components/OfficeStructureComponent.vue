<template>
 <div class="form-group">
    <label for="offices">Office</label>
        <VoerroTagsInput 
            :existing-tags="tags"
            :typeahead="true" v-model="ids" elementId="user_to_office_id" @tags-updated="assignOffice">
        </VoerroTagsInput>
</div>    
</template>

<script>
import VoerroTagsInput from '@voerro/vue-tagsinput';
    export default {
        mounted() { 
            console.log(this.ids);
            axios.get('/api/structure') 
                .then(res=>{
                    this.offices = res.data
                })
        },
        components : {
            VoerroTagsInput
        },
        props: ['structure-type','default'],
        computed: {
            tags(){
                var offices = [];
                $.each(this.offices,function(k,v){
                    offices.push({key:v.id,value:v.name});
                });

                return offices;
            }
        },
        data(){
            return {
                offices: null,
                el: null,
                ids:[]                
            }
        },
        methods:{
            assignOffice(){
                if(this.ids!=null){
                    this.$emit('officeSelected', this.ids);
                }
            }
        },
        watch:{
            default : function (newVal,oldVal){
                var vm = this
               if (newVal) {
                    $.each(newVal, function(k,v){
                        vm.ids.push({key:v.id,value:v.name});
                    });
                    return;
               }
            }
        }
    }
</script>
