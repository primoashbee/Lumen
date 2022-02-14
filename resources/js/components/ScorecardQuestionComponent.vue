<template>
    <div>
        <div>
            {{ questions }}
            <div class="form-group" v-for="(choice,index) in choices" :key="index">
                <input 
                    :id="index" 
                    :name="index" 
                    :value="choice" 
                    :checked="false" 
                    v-model="results"
                    type="radio"
                    @change="doSomething(questionNumber,choice.result,choice.text)"
                    >
                <label :for="'answer'+index">{{ choice.text }}</label>
            </div>
        </div>
        {{results}}
    </div>
</template>

<script>

export default {
    props:['questionNumber','questions','choices'],
    data(){
        return {
            results:null,
            questionIndex: 0
        }
    },
    methods:{
    answer_picked(question,value){
        console.log(question,value)
        return this.results.push(value)
    },
    doSomething(q_number,a_score,a_answer){
        this.results= {
            questionNumber:q_number,
            score:a_score,
            answer:a_answer
        }
        
            if(this.results!=null){
                this.$emit('answerPicked', this.results);
            }
        },
    }
    
}
</script>