 // Vuex Store
 const store = new Vuex.Store({
    modules: {
        propietarios: propietarioModule
    },
    state: {
      message: 'Mensaje inicial'
    },
    mutations: {
      setMessage(state, newMessage) {
        state.message = newMessage;
      }
    },
    actions: {
      changeMessage({ commit }, newMessage) {
        commit('setMessage', newMessage);
      }
    }
  });
