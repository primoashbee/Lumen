<template>
 <div class="form-group">
    <label for="roles">Roles</label>
        <VoerroTagsInput 
            :existing-tags="tags"
            :typeahead="true" v-model="ids" elementId="roleId" @tags-updated="roleUpdate">
        </VoerroTagsInput>
</div>    
</template>

<script>
import VoerroTagsInput from '@voerro/vue-tagsinput';
    export default {
        props: ['default'],
        mounted() { 
            axios.get('/roles/list') 
                .then(res=>{
                    this.roles = res.data
                })

                if (this.default !== null) {
                var vm = this
                $.each(this.default, function(k,v){
                        vm.ids.push({key:v.id,value:v.name});
                    });
                }
        },
        components : {
            VoerroTagsInput
        },
        computed: {
            tags(){
                var roles = [];
                $.each(this.roles,function(k,v){
                    roles.push({key:v.id,value:v.name});
                });

                return roles;
            },
        },
        data(){
            return {
                roles: null,
                el: null,
                ids:[]
            }
        },
        methods:{
            roleUpdate(){
                if(this.ids!=null){
                    this.$emit('roleSelected', this.ids);
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
