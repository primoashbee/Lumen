<template>
</template>
<script>
import 'vuejs-noty/dist/vuejs-noty.css'
export default {
    props : ['office_id'],
    created(){

        window.Echo.private('dashboard.notifications.'+this.office_id)
            .listen('.loan-disbursed',data =>{
                console.log(data.data.msg);
                this.notify(data.data.msg);
            })
            .listen('.bulk-loan-payment',data =>{
                this.notify(data.data.msg)
            })
            .listen('.loan-payment',data =>{
                this.notify(data.data.msg)
            })
            .listen('.cbu-deposit',data =>{
                this.notify(data.data.msg)
            })
            .listen('.cbu-withdraw',data =>{
                this.notify(data.data.msg)
            })
            .listen('.cbu-interest-posting',data =>{
                this.notify(data.data.msg)
            })

        // window.Echo.private('group.channel.'+this.office_id)
        // .listen('.suntukan-tayo',data=>{
        //     this.notify(data.msg)
        // })

        // window.Echo.join(this.channel)
        // .here((users)=>{
        //     console.log(users)
        // })
        // .joining((user)=>{
        //     console.log(user)
        // })
        // .leaving((user)=>{
        //     console.log(user)
        // })
        // .listen('.suntukan-tayo', data =>{
        //     this.notify(data.msg)
        // })



    },
    methods :{
        notify(msg){
            Noty.setMaxVisible(25)
            // Noty.setProgressBar(true)
            new Noty({
                theme:'sunset',
                type: 'success',
                layout: 'topRight',
                text: msg,
                timeout: 6000,
                // animation: {
                //     open : 'animated fadeInRight',
                //     close: 'animated fadeOutRight'
                // }
            }).show();
    
            
        }
    },
    computed: {
        // channel(){
        //     return 'presence.channel.'+this.office_id;
        // }
    }
}
</script>




