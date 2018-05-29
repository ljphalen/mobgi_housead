function draw(lineData, hTitle, yTitle) {
	Highcharts.setOptions({
	    global: {
	        useUTC: false
	    }
	});
	chart = new Highcharts.Chart({
				chart : {
					renderTo : 'container',
					type : 'spline'
				},
				title : {
					text : hTitle
				},
				exporting : {
					enabled : true
				},
				credits : {
					enabled : false
				},
				legend : {
					verticalAlign : 'bottom',
					y : 0,
					backgroundColor : '#FFFFFF',
					borderWidth : 0,
					itemStyle : {
						color : '#000000',
						fontWeight : 'bold'
					},
					labelFormatter : function() {
						return this.name + '(点击隐藏)';
					},
					symbolPadding : 10
				},
				xAxis : {
					type : 'datetime',
					dateTimeLabelFormats : {
						second : '%Y-%m-%d<br/>%H:%M:%S',
						minute : '%Y-%m-%d<br/>%H:%M',
						hour : '%Y-%m-%d<br/>%H:%M',
						day : '%Y<br/>%m-%d',
						week : '%Y<br/>%m-%d',
						month : '%Y-%m',
						year : '%Y'
					}
				},
				yAxis : {
					title : {
						text : yTitle
					},
					min : 0
				},
				tooltip : {
					formatter : function() {
						return '<b>' + this.series.name + '</b><br/>'
								+ Highcharts.dateFormat('%Y-%m-%d', this.x)
								+ ': ' + this.y;
					}
				},
				series : lineData
			});
}