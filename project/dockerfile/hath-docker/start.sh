HATH_PARAMS="";

for i in $(seq 3 $#); do
    HATH_PARAMS="${HATH_PARAMS} "eval echo \$$i
done

if [ -n "$1" ] && [ -n "$2" ]; then
    if [ ! -d "./data" ]; then
        mkdir ./data \
        && echo -n "${1}-${2}" > ./data/client_login  
    fi
    
    java -jar HentaiAtHome.jar --port=443 $HATH_PARAMS
fi



