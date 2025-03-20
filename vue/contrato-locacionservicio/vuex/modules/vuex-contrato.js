const ContratoModule = {
    namespaced: true,
    state() {
      return {
        contratos:[],
      }
    },
    mutations: {
      mLoadAgregarContrato(state, data_contrato){
        state.contratos.push(data_contrato)
      },
      mLoadEliminarContrato(state, index){
        state.contratos.splice(index, 1);
      },
    },
    actions: {
      ActionAgregarContrato({ commit, state }, data_contrato){
        commit('mLoadAgregarContrato',data_contrato)
      },
      ActionEliminarContrato({ commit, state }, index){
        commit('mLoadEliminarContrato',index)
      }
    }
};