class globalVars{

    getApiUrl(useServerAPI){
        if(useServerAPI){
            return "http://162.243.167.200:9999";
        }else{
            return "http://localhost:9999";
        }
    }

}