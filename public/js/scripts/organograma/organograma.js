$(document).ready(function () {

    loadMsg();
    // ajax principal
    $.ajax({
        url:  'organograma/get-coordenacoes',
        async: true,
        type: 'POST',
        data: '',
        dataType: 'json',
        success: function (retorno) {
            var org = new Organogram('ROOT', retorno.coords, retorno.legRoot, retorno.legends);
            org.createSquares();
            hideMsg();
        },
        error: function( jqXHR ,  textStatus,  errorThrown ){
            console.log(textStatus);
            console.log(errorThrown);
        }
    });
});


function Organogram(back, retorno, leg, tot) {

    this.legRoot = leg[0];
    this.totais = tot[0];
    this.backStack = [];
    this.backStack.push(back);
    this.coords = retorno;
    this.graph = new joint.dia.Graph();
    this.paper = new joint.dia.Paper({
        el: $('#Graficos'),
        width: 1000,
        height: 2500,
        gridSize: 1,
        model: this.graph,
        interactive:false,
        perpendicularLinks: true,
        restrictTranslate: true
    });
    that = this;
    this.paper.on('cell:pointerdown',
        function(cellView, evt, x, y) {
            a = cellView.model.attr('.rank/text');
            that.requestJson(a);

        });

    this.suporte = ["CEDESBR010","CEDESBR020","CEDESBR030","CEDESBR040","CEDESBR050","CEDESBR060","CEDESBR090","CEDESBR100"];

    this.backBtn  =  $('#btnBackOrgan');

    this.backBtn.on('click',
        function(button, evt, x, y) {
            var a = that.backStack.pop();
            a = that.backStack.pop();
            if (a === undefined ) {
                that.backStack.push("ROOT");
                a = 'ROOT';
            }
            if (a == "ROOT") {
                loadMsg();
                that.backStack.push("ROOT");
                that.createSquares();
            } else{
                that.requestJson(a);
            }
        });

    String.prototype.capitalize = function() {
        return this.replace(/\w\S*/g, function(txt){
            return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
    };

    String.prototype.firstLast = function() {
        tmp = this.split(" ");
        return tmp[0]+" "+tmp[tmp.length-1];
    };

    String.prototype.contains = function(it) {
        return this.indexOf(it) != -1;
    };

    Organogram.prototype.member = function (x, y, rank, name, image, background, textColor) {
        textColor = textColor || "#000";


        var cell = new joint.shapes.org.Member({
            position: { x: x, y: y },
            attrs: {
                '.card': { fill: background, stroke: 'none'},
                image: { 'xlink:href': image, opacity: 0.7 },
                '.rank': { text: rank, fill: textColor, 'word-spacing': '-5px', 'letter-spacing': 0},
                '.name': { text: name, fill: textColor, 'font-size': 13, 'font-family': 'Arial', 'letter-spacing': 0 }
            }
        });

        this.graph.addCell(cell);


        new joint.ui.Tooltip({
            target: cell,
            content: 'Top directed tooltip.',
            top: '.top-tooltip',
            direction: 'top'
        });


        return cell;
    };

    Organogram.prototype.link = function(source, target, breakpoints) {
        var cell = new joint.shapes.org.Arrow({
            source: { id: source.id },
            target: { id: target.id },
            vertices: breakpoints,
            attrs: {
                '.connection': {
                    'fill': 'none',
                    'stroke-linejoin': 'round',
                    'stroke-width': '2',
                    'stroke': '#4b4a67'
                }
            }
        });
        this.graph.addCell(cell);
        return cell;
    };

    Organogram.prototype.legend = function (x, y, rank, name, morename, background, textColor) {
        textColor = textColor || "#000";
        var cell = new joint.shapes.org.Legend({
            position: { x: x, y: y },
            attrs: {
                '.card': { fill: background, stroke: 'none'},
                '.rank': { text: rank, fill: textColor, 'word-spacing': '-5px', 'letter-spacing': 0},
                '.name': { text: name, fill: textColor, 'font-size': 13, 'font-family': 'Arial', 'letter-spacing': 0 },
                '.morename': { text: morename, fill: textColor, 'font-size': 13, 'font-family': 'Arial', 'letter-spacing': 0 }
            }
        });

        this.graph.addCell(cell);
        return cell;
    };

    Organogram.prototype.requestJson = function(first_argument) {
        if (first_argument.contains("CEDESBR")) {
            this.backStack.push(first_argument);
            loadMsg();
            that = this;
            $.ajax({
                url:  'organograma/get-sub-coordenacoes',
                async: true,
                type: 'POST',
                data: 'coord=' + first_argument,
                dataType: 'json',
                success: function (retorno) {
                    hideMsg();
                    that.createSubSquares(retorno);
                },
                error: function( jqXHR ,  textStatus,  errorThrown ){
                    console.log(textStatus);
                    console.log(errorThrown);
                }
            });
        }
    };

    Organogram.prototype.createSquares = function() {
        hideMsg();
        this.graph.clear();
        var root = null;
        var cell = null;
        var baseUrlImg = '/img/';

        legend = this.legend(
            750,0,
            this.reduc(this.legRoot.de_area),
            this.legRoot.no_funcionario.capitalize(),
            'Empregados: ' +this.totais.total,
            '#c8c8c8'
            );

        root = this.member(410,0,
            this.coords[0].label ,
            this.coords[0].nome.firstLast().toLowerCase().capitalize(),
            baseUrlImg + 'male-w.png',
            '#30d0c6');
        that = this;
        var suporte = _.filter(this.coords,
            function(coord){
                var ret = false;
                if (that.suporte.indexOf(coord.label) != -1) {
                    ret = true;
                }
                return ret;
            });

        var desenv = _.difference(this.coords, suporte);
        desenv = _.reject(desenv,{label : this.coords[0].label } );


        for (var i = 0; i < suporte.length; i++) {
            suporte[i].foto  = 'suporte-w.png';
            suporte[i].color =  '#feb563';
        }

        for (var j = 0; j < desenv.length; j++) {
            desenv[j].foto  = 'desenv-w.png';
            desenv[j].color =  '#99bbff';
        }

        var splits = this.groups(desenv,2) ;
        this.treeColumns(
            baseUrlImg,
            root,suporte,splits[0],splits[1]
            );
    };

    Organogram.prototype.createSubSquares = function(args) {
        console.log(args.root[0].de_area);
        this.graph.clear();
        var root = null;
        var baseUrlImg = 'http://sipti.caixa/sipti/image/';
        var splits = null;
        root = this.member(410,0,
            args.root[0].label,
            this.reduc2(args.root[0].de_area),
            '/indicadores/img/male-w.png',
            '#30d0c6');

        var clean = args.funcionarios;

        if (args.root[0].label == 'CEDESBR'){
            legend = this.legend(
                750,0,
                this.reduc(this.legRoot.de_area),
                this.legRoot.no_funcionario.capitalize(),
                'Empregados: ' + this.totais.total,
                '#c8c8c8'
                );
            splits = this.groups(clean,4);
            this.fourColumns(
                baseUrlImg,
                root,splits[0],splits[1],
                splits[2],splits[3]
                );
        } else{
            legend = this.legend(
                750,0,
                this.reduc(args.root[0].label),
                'Resp.: '+ args.root[0].nome.capitalize().firstLast(),
                'Empregados: ' + args.legends[0].total,
                '#c8c8c8'
                );
            if ( args.funcionarios.length > 10 && args.coords >=1){
                splits = this.groups(args.funcionarios,2);
                this.treeColumns(
                    baseUrlImg,
                    root,args.coords,splits[0],splits[1]
                    );
            }else {
                for (var i = 0; i < args.coords.length; i++) {
                    args.coords[i].color =  '#feb563';
                }
                for (var j = 0; j < args.funcionarios.length; j++) {
                    args.funcionarios[j].color =  '#99bbff';
                }
                if (args.coords.length >= 1) {
                    this.twoColumns(
                        baseUrlImg,
                        root,args.coords, args.funcionarios
                        );
                } else{
                    splits = this.groups(args.funcionarios,2);
                    this.twoColumns(
                        baseUrlImg,
                        root,splits[0], splits[1]
                        );
                }
            }
        }
    };

    Organogram.prototype.twoColumns = function(baseUrlImg,root,fst,scnd) {
        var coef = 1;
        for (var i = 0; i < fst.length; i++) {
            cell = this.member(160, 100 * coef,
                this.formatFunc(fst[i].label),
                fst[i].nome.firstLast().toLowerCase().capitalize() ,
                baseUrlImg + fst[i].foto,
                fst[i].color);
            coef++;
            this.link(root, cell, [{x: 490, y: (100 * coef) -70 }]);
        }
        coef = 1;
        for (var j = 0; j < scnd.length; j++) {

            cell = this.member(660, 100 * coef,
                this.formatFunc(scnd[j].label),
                scnd[j].nome.firstLast().toLowerCase().capitalize() ,
                baseUrlImg + scnd[j].foto,
                scnd[j].color);
            coef ++;
            this.link(root, cell, [{x: 510, y: (100 * coef) -70 }]);
        }
    };

    Organogram.prototype.treeColumns = function(baseUrlImg,root,fst,scnd,trd) {
        var coef = 1;
        for (var i = 0; i < fst.length; i++) {
            cell = this.member(35, 100 * coef,
                this.formatFunc(fst[i].label),
                fst[i].nome.firstLast().toLowerCase().capitalize() ,
                baseUrlImg + fst[i].foto,
                fst[i].color);
            coef++;
            this.link(root, cell, [{x: 313, y: 70},{x: 313, y: (100 * coef) -70 }]);
        }
        coef = 1;
        for (var j = 0; j < scnd.length; j++) {

            cell = this.member(410, 100 * coef,
                this.formatFunc(scnd[j].label),
                scnd[j].nome.firstLast().toLowerCase().capitalize() ,
                baseUrlImg + scnd[j].foto,
                scnd[j].color);
            coef ++;
            this.link(root, cell, [{x: 650, y: 70},{x: 650, y: (100 * coef) -70 }]);
        }
        coef = 1;
        for (var k = 0; k < trd.length; k++) {

            cell = this.member(785, 100 * coef,
                this.formatFunc(trd[k].label),
                trd[k].nome.firstLast().toLowerCase().capitalize() ,
                baseUrlImg + trd[k].foto,
                trd[k].color);
            coef ++;
            this.link(root, cell, [{x: 688, y: 70},{x: 688, y: (100 * coef) -70 }]);
        }
    };

    Organogram.prototype.fourColumns = function(baseUrlImg, root,fst,scnd,trd,frh) {
        var coef = 1;
        for (var i = 0; i < fst.length; i++) {
            cell = this.member(35, 100 * coef,
                this.formatFunc(fst[i].label),
                fst[i].nome.firstLast().toLowerCase().capitalize() ,
                baseUrlImg + fst[i].foto,
                fst[i].color||'#99bbff');
            coef++;
            this.link(root, cell, [{x: 245, y: 70},{x: 245, y: (100 * coef) -70 }]);
        }
        coef = 1;
        for (var j = 0; j < scnd.length; j++) {

            cell = this.member(285, 100 * coef,
                this.formatFunc(scnd[j].label),
                scnd[j].nome.firstLast().toLowerCase().capitalize() ,
                baseUrlImg + scnd[j].foto,
                scnd[j].color||'#99bbff');
            coef ++;
            this.link(root, cell, [{x: 490, y: (100 * coef) -70 }]);
        }
        coef = 1;
        for (var k = 0; k < trd.length; k++) {

            cell = this.member(535, 100 * coef,
                this.formatFunc(trd[k].label),
                trd[k].nome.firstLast().toLowerCase().capitalize() ,
                baseUrlImg + trd[k].foto,
                trd[k].color||'#99bbff');
            coef ++;
            this.link(root, cell, [{x: 510, y: (100 * coef) -70 }]);
        }
        coef = 1;
        for (var l = 0; l < frh.length; l++) {
            cell = this.member(785, 100 * coef,
                this.formatFunc(frh[l].label),
                frh[l].nome.firstLast().toLowerCase().capitalize() ,
                baseUrlImg + frh[l].foto,
                frh[l].color||'#99bbff');
            coef ++;
            this.link(root, cell, [{x: 750, y: 70},{x: 750, y: (100 * coef) -70 }]);
        }
    };

    Organogram.prototype.formatFunc = function(args) {
        if (args == 'Coordenador de Projetos de TI') {
            return 'CPTI';

        }else if(args == 'Assistente Junior'){
            return 'Asst. Jr.';

        }else if(args == 'Gerente de Centralizadora'){
            return 'Ger. Centraliz.';

        }else if(args == 'Consultor de TI '){
            return 'Cons. TI';

        }else if(args == 'Líder de Projeto de Tecnologia'){
            return 'LPTI';

        }else if(args == 'Supervisor de TI'){
            return 'Sup. TI';

        }else if(args == 'Coordenador de TI'){
            return 'CTI';

        }else if(args == 'Assistente Sênior'){
            return 'Asst. Sr.';

        }else if(args == 'Assistente Pleno'){
            return 'Asst. Pl.';

        }else if(args == 'Analista Júnior 6h FII'){
            return 'Anl.Jr. 6h FII';

        }else if(args == 'Especialista 6h'){
            return 'Esp. 6h';

        }else if(args == 'Analista Sênior 8h'){
            return 'Anl.Sr. 8h';

        } else return args;
    };

    Organogram.prototype.groups = function (array, cols) {
        function split(array, cols) {
            if (cols==1) return array;
            var size = Math.ceil(array.length / cols);
            return array.slice(0, size)
            .concat([null]).concat(split(array.slice(size), cols-1));
        }
        var a = split(array, cols);
        var groups = [];
        var group = [];
        for(var i = 0; i < a.length; i++) {
            if (a[i] === null) {
                groups.push(group);
                group = [];
                continue;
            }
            group.push(a[i]);
        }
        groups.push(group);
        return groups;
    };

    Organogram.prototype.reduc2 = function(args) {
        args = args.replace('Coordenação de', '');
        args = args.replace('Coordenação', '');
        args = args.replace('Coordenação', '');
        args = args.replace('Processo', '');
        args = args.replace('Subcontratação', 'Subcontr.');
        args = args.replace('Conformidade', 'Conform.');
        args = args.replace('Escritório', 'Escr.');
        args = args.replace('Projetos', 'Proj.');
        args = args.replace('Desenvolvimento', 'Desenv.');
        args = args.replace('Gestão de', '');
        return args;

    };

    Organogram.prototype.reduc = function(args) {
        return args.replace("Desenvolvimento", "Desenv.");
    };

}
