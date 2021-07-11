<template>
	<div>

		<b-button :id="fields.id" @click="showModal" class="dropdown-item d-text">Change Password</b-button>

		<b-modal id="myModal" size="lg" v-model="show" hide-footer modal-title="Change Password" title="Change Password" :header-bg-variant="background" :body-bg-variant="background">
	        <form @submit.prevent="submit">
	            <div class="form-group">
	                <label class="text-lg" for="password"> Password</label>
	                <input type="password" v-model="fields.password" id="password" class="form-control">
	            </div>
	            <div class="form-group">
	                <label class="text-lg" for="password-confirm">Confirm Password</label>
	                <input type="password" v-model="fields.password_confirmation" id="password_confirmation" class="form-control">
	            </div>
	            <button type="submit" class="btn btn-primary">Submit</button>
	        </form>
	    </b-modal>
    </div>
</template>

<style>
    .btn.dropdown-item.d-text.btn-secondary:focus{
        color: #16181b;
        text-decoration: none;
        background-color: transparent;
        box-shadow: transparent;
    }
</style>

<script>
	export default{
		props:['user_id'],
		data(){
			return {
				variants: ['primary', 'secondary', 'success', 'warning', 'danger', 'info', 'light', 'dark'],
                background:'dark',
                show:false,
                errors:{},
                fields:{
                    id:"",
                    password:"",
                    password_confirmation:""
                }
			}
		},
        mounted(){
            this.fields.id = JSON.parse(this.user_id).id
        },
		methods:{
			userLink(value){
                return '/user/changepass/'+value
            },
            showModal(e){
            	this.show = true
                this.fields.id = e.currentTarget.getAttribute('id')
            },
            submit(){
                console.log(this.userLink(this.fields.id));
                axios.post(this.userLink(this.fields.id), this.fields).
                then(res => {
                    Swal.fire({
                        icon: 'success',
                        title: '<span style="font-family:\'Open Sans\', sans-serif!important;color:black;font-size:1em;font-weight:600">Success!</span>',
                        text: res.data.msg,
                        confirmButtonText: 'OK',
                        allowEnterKey: true // default value
                        })
                        .then(res=>{
                            location.reload()
                        })
                })
            },
		}
	}
</script>