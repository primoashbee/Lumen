<template>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle l-text" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false"><i class="fas fa-bell"></i></a>
        <div class="dropdown-menu notification-list">
        <ul>
            <li v-for="(item, key) in list" :key="key">
                <a class="dropdown-item d-text" v-bind:class="{gg : !item.seen}" :href="item.link">{{item.msg}}</a>
            </li>

        </ul>
        </div>
    </li>   
</template>

<script>
export default {
    props : ['user'],
    data (){
        return {
            list : [
                {
                    link: "https://www.youtube.com/watch?v=xspg7SNxTWA",
                    msg: "System - Lorem Ipsum is simply dummy text of the printing and typesetting industry",
                    seen: false,
                    seen_at: null,
                    created_at: new Date()
                },
                {
                    link: "https://www.youtube.com/watch?v=xspg7SNxTWA",
                    msg: "System - It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout.",
                    seen: false,
                    seen_at: null,
                    created_at: new Date()
                },
                {
                    link: "https://www.youtube.com/watch?v=xspg7SNxTWA",
                    msg: "System - There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour",
                    seen: true,
                    seen_at: null,
                    created_at: new Date()
                }
            ]
        }
    },
    mounted(){
        window.Echo.private('user.notification.'+this.user)
            .listen('.notify-user', data =>{
                this.list.unshift(data.notification)
            });
    },
    methods : {

    }
}
</script>

<style scoped>

.gg {
    background: rgba(128, 128, 128, 0.3)
}

.notification-list {
    width: 300px !important;

}
.notification-list ul>li>a {
    white-space:normal;


}
</style>