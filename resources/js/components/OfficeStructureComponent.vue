multi<template>
 <div class="form-group row">
    <label for="offices" class="col-md-4 col-form-label text-md-right">Office</label>

    <div class="col-md-6"> 
        <VoerroTagsInput 
            :existing-tags="tags"
            :typeahead="true" v-model="ids" elementId="user_to_office_id">
        </VoerroTagsInput>

    </div>

</div>    
</template>

<script>
import VoerroTagsInput from '@voerro/vue-tagsinput';
    export default {
        mounted() { 
            axios.get('/api/structure') 
                .then(res=>{
                    this.offices = res.data
                })
        },
        components : {
            VoerroTagsInput
        },
        props: ['structure-type'],
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
        }
    }
</script>
