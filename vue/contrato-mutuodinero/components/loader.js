const template_loader = {
    template:`
    <div v-if="loader" class="loader-container">
        <span class="loader-template"></span> <br>
    </div>
    `,
    props:["loader"],

};

Vue.component("loader", template_loader);
