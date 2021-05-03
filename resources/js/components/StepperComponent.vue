<template>
    <stepper :options="stepperOptions">
        <div slot="step-1">
            <h3>Step 1</h3>
            Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque 
        </div>
        
        <div slot="step-2">
            <h3>Step 2</h3>
            Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque 
        </div>
        
        <div slot="step-3">
            <h3>Step 3</h3>
            Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque 
        </div>
        
        <div slot="custom-step-name">
            <h3>Step 4</h3>
            Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque 
        </div>
    </stepper>
</template>
 
<script>
import Stepper from 'vuejs-stepper'
export default {
  components: { Stepper },
  data () {
    return {
      stepperOptions: {
        headers: [
          {title: 'Title One'},
          {title: 'Title Two'},
          {title: 'Title Three'},
          {title: 'Title Four', stepName: 'custom-step-name'}
        ],
        prevText: 'Previous',
        nextText: 'Next'
      }
    }
  }
};
</script> 