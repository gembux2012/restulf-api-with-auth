class wsoket {
    socket ;
    onOpen;
    onSigned;

    onsigned() {
        console.log('signed')
        this.onSigned.call()
    }


    onopen() {
        this.socket.onmessage =(data)=> {
            console.log(data)
            switch (JSON.parse(data.data).type) {
                case 'session':
                    this.session = (JSON.parse(data.data));
                    break;

                case 'subscribe':
                    console.log(JSON.parse(data.data.subscribe));
                    break;

                case 'signed':

                    this.onsigned();
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
        console.log('open',this.socket)
        this.socket.send(msg);
    }

    subscribe(topic, onevent) {
        console.log('subscriber')
        let msg = {'type': 'subscribe', 'topic': topic, 'callback': onevent}
        this.socket.send(JSON.stringify(msg));
    }

    publish(topic, data) {
        console.log('publish')
        let msg = {'type': 'publish', 'topic': topic, 'data': data}
        this.onSigned = () => this.socket.send(JSON.stringify(msg))

    }

    messageEvent(){

    }


}

wm = new wsoket();
wm.open();
wm.onOpen=function(){
    wm.subscribe('all');
    wm.publish('all');
}

console.log(wm);


function setNode(parent, chield) {

    $(parent).load("http://localhost:8000/" + chield, function () {
            //socket();
        }
    )
}

