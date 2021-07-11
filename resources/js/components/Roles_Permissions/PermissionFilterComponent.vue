<template>
 <div class="form-group">
    <label for="roles">Permissions</label>
        <VoerroTagsInput 
            :existing-tags="tags"
            :typeahead="true" v-model="ids" elementId="permissionId" @tags-updated="permissionUpdate">
        </VoerroTagsInput>
</div>    
</template>

<style>
    .tags-input input[type="text"]{
        color: #fff!important;
    }
    .tags-input-wrapper-default{
        background: transparent;
    }
    .tags-input-wrapper-default.active{
        border:1px solid #fff;
        box-shadow: none;
    }
</style>

<script>
import VoerroTagsInput from '@voerro/vue-tagsinput';
    export default {
        props: ['default'],
        mounted() { 
            axios.get('/permissions/list') 
                .then(res=>{
                    this.permissions = res.data
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
                var permissions = [];
                $.each(this.permissions,function(k,v){
                    permissions.push({key:v.id,value:v.name});
                });

                return permissions;
            },
        },
        data(){
            return {
                permissions: null,
                el: null,
                ids:[]
            }
        },
        methods:{
            permissionUpdate(){
                if(this.ids!=null){
                    this.$emit('permissionSelected', this.ids);
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
