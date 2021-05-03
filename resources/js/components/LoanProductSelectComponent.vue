<template>
    <select class="d-inline-block form-control" @change="selected">
        <option :value="null">Please Select</option>
        <option v-for="item in list" :key="item.id" :value="JSON.stringify({id:item.id,code:item.code})" :data-name="item.code">{{item.name}}</option>
    </select>
</template>

<script>
export default {
    props: ['id'],
    data(){
        return {
            code: null,
            list : [],
            rates: []
        }
    },
    mounted(){
        this.fetch()
        
    },
    methods : {
        selected(e){
            
            let selected = JSON.parse(e.target.value)
            this.code = selected.code
            selected.rates = this.installment_list
            
            this.$emit('selected',selected)
        },
        fetch(){
            axios.get('/loan/products?has_page=false')
            .then(res=>{
                this.list = res.data.loans
                this.rates = res.data.rates
            })
        }
    },

    computed:{
        installment_list(){
			if(this.code == null){
				return null;
			}	
			let obj = []
			let rates;
			this.rates.map(x=>{
				if(x.code == this.code){
					x.rates.map(r=>{
						obj.push(r)
					})
				}
			});

			
			return obj;
			
        },
        selected_interest(){
			let vm = this;
			if(vm.installment_list==null){
				return null;
			}

			var rate = null
			vm.installment_list.map(x=>{ 
				if(x.installments == vm.form.number_of_installments){
					rate = x.rate;
				}
			});
			return rate;
		}
    }
    
}
</script>