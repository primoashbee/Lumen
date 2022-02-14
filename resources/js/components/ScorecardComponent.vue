<template>
    <div>
        <div v-for="(question,index) in questionaire" :key="question.id">
            <scorecard-question 
                :questions="question.text" 
                :choices="question.choices"
                :questionNumber="index"
                @answerPicked="recordAnswer">
                </scorecard-question> 
        </div>
    </div>
</template>

<script>
import ScorecardQuestionComponent from "./ScorecardQuestionComponent"

export default {
    data(){
        return {
            results:[],
            questionIndex: 0,
            questionaire:[
                {
                    text:"Question 1",
                    choices:[
                        {
                            text : "a",
                            result:10
                        },
                        {
                            text : "b",
                            result:5
                        }
                    ]
                },
                {
                    text:"Question 2",
                    choices:[
                        {
                            text : "a",
                            result:10
                        },
                        {
                            text : "b",
                            result:5
                        }
                    ]
                },
                {
                    text:"Question 3",
                    choices:[
                        {
                            text : "a",
                            result:10
                        },
                        {
                            text : "b",
                            result:5
                        }
                    ]
                },
                {
                    text:"Question 4",
                    choices:[
                        {
                            text : "a",
                            result:10
                        },
                        {
                            text : "b",
                            result:5
                        }
                    ]
                }
            ]
            
        }
    },
    methods:{
        answer_picked(question,value){
            console.log(question,value)
            return this.results.push(value)

        },
        recordAnswer(value){
            this.results.push(value)
        }
    },
    computed:{

    }
}
</script>