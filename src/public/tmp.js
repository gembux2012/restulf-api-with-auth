class wsoket {
    socket ;
    onOpen;
    onSigned;
    onSubscriber;


    onsigned() {
        console.log('signed')
        this.onSigned.call()
    }


    onopen() {
        this.socket.onmessage =(data)=> {
            //console.log(data)
            switch (JSON.parse(data.data).type) {
                case 'session':
                    this.session = (JSON.parse(data.data));
                    break;

                case 'subscribe':
                    //console.log(JSON.stringify(JSON.parse(data.data).data))

                    this.onSubscriber(JSON.parse(data.data).data);
                    break;

                case 'signed':
                    this.onSigned();
                    break;
            }

        };



        this.socket.onclose = function (event) {
            alert(`[close] Соединение закрыто, код=${event.code} причина=${event.reason}`);
            reject('close')
        };


        console.log('open',this.socket)
        this.onOpen.call();

    };

    onSubsriber(data){
        //console.log(JSON.parse(data.data).callback);
            this.onSubscriber.call(data)



        //console.log(JSON.parse(data.data).data);
    }
 

    init() {
        return new Promise(function (resolve, reject) {
            let wsClient = new WebSocket("ws://localhost:8080");
            wsClient.onopen = () => {
                console.log("connected");
                resolve(wsClient);
            };

            wsClient.onerror = (error) => {
                reject(error);
            };


        })
    }


    open() {
        this.init().then(socket => {
            this.socket = socket;

            this.onopen();

        })
            .catch(error => console.log(error));
    }





    send(msg) {
        //console.log('open',this.socket)
        this.socket.send(msg);
    }

    subscribe(topic, onevent) {
       // console.log('subscriber')
        let msg = {'type': 'subscribe', 'topic': topic, 'callback': onevent}
        this.socket.send(JSON.stringify(msg));
    }

    publish(topic, data) {
        //console.log('publish')
        let msg = {'type': 'publish', 'topic': topic, 'data': data}
        this.onSigned = () => this.socket.send(JSON.stringify(msg))

    }

    messageEvent(){

    }


}


