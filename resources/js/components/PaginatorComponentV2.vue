<template>
    <div>
        <nav aria-label="page-navigation" v-if="shouldPaginate" >
        <ul class="pagination">
            <li class="page-item">
            <a class="page-link" href="#" aria-label="Previous" @click.prevent="firstPage" style="color:black">
                <!-- <span aria-hidden="true">&laquo;</span> -->
                First
            </a>
            </li>
            <li class="page-item" v-show="showPrev" >
            <a class="page-link" href="#" aria-label="Previous" @click.prevent="page--" style="color:black">
                <span aria-hidden="true">&laquo;</span>
                <span class="sr-only">Previous</span>
            </a>
            </li>
            <li class="page-item" v-bind:class="item ==page?'page-active':''" v-for="(item, key) in pageList" :key="key">
                <a class="page-link" style="color:black"  href="#" @click.prevent="changePage(item)">{{item}}</a>
            </li>
            <li class="page-item" v-show ="showNext">
            <a class="page-link" href="#" style="color:black" aria-label="Next" @click.prevent="page++">
                <span aria-hidden="true">&raquo;</span>
                <span class="sr-only">Next</span>
            </a>
            </li>
            <li class="page-item">
            <a class="page-link" href="#" aria-label="Previous" @click.prevent="lastPage" style="color:black">
                <!-- <span aria-hidden="true">&raquo;</span> -->
                Last
            </a>
            </li>
        </ul>
        </nav>
    </div>
</template>

<script>
export default {
    props : ['dataset'],
    data(){
        return {
            page: 1,
            prevUrl : null,
            nextUrl: null,
            total_pages: 0,
            last_page: null,
            first_page: null,
            // pages : [],
            
        }
    },
    methods: {
       broadcast(){
           this.$emit('pageSelected',this.page)
       },
       changePage(page){
           this.page = page
       },
       lastPage(){
           this.page= this.last_page
       },
       firstPage(){
           this.page = 1
       }
    },
    computed : {
        shouldPaginate(){
            if(this.last_page != null){
                return true;
            }
            return false;
        },
        showNext(){
            if(this.page == this.last_page){
                return false
            }
            return true;
        },
        showPrev(){
            if(this.page == this.first_page){
                return false
            }
            return true;
        },
        pageList(){
            // if(this.last_page > 10){

            //     var pages = [];
            //     //add 5 before
            //     //add 5 after
            //     for(var x = this.page; x<=this.page + 10;x++){
            //             pages.push(x)
            //     }
            //     return pages;
            // }
            var pages = [];
            if(this.last_page === null){
                return [];
            }
            if(this.last_page < 11){
                for(x=1; x<=this.last_page; x++){
                    pages.push(x)
                }
                return pages;
            }
            if(this.page > 5 && (this.last_page - this.page > 5)){
                var last_visible_page = this.page  + 5  > this.last_page ? this.last_page - this.page  : this.last_page
                for(var x = this.page-5; x<= last_visible_page; x++){
                    pages.push(x)
                }
                return pages;
            }else if((this.page + 6)  > this.last_page){
                for(var x = this.last_page - 10; x <= this.last_page; x++){
                    pages.push(x)
                }
                return pages;
            }else{
                for(var x = 1; x<=10; x++){
                    pages.push(x);
                }
                return pages;
            }
        }
    },
    watch : {
        dataset : {
            immediate: true,
            handler(newVal,oldVal){
                if(newVal.hasOwnProperty('data')){
                    
                    if(newVal.data.length > 0){
                    // this.pages = newVal.last_page
                        this.first_page = 1
                        this.last_page = newVal.last_page
                        this.prevUrl = true
                        this.nextUrl = true
                        this.pagesList = []
                    }else{
                        // this.pages = []
                        this.pagesList = []
                        this.first_page = null
                        this.last_page = null
                        this.prevUrl = false
                        this.nextUrl = false
                    }
                }else{
                    this.pages = []
                    this.first_page = null
                    this.last_page = null
                    this.prevUrl = false
                    this.nextUrl = false
                }
                
            }
        },
        page: {
            handler(){
                this.broadcast()
            }
        }

    }
    
}
</script>

<style scoped>
.page-active{
    background-color: #fdad7d !important;
}
</style>