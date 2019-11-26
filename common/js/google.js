//--------------------------------------
// Google Maps 表示（jQuery.js）
// Google Maps API V3
//
// 本プログラムの著作権は「株式会社プラソル」にあります。
// 本プログラムを無断で、転記、改造、販売を行う事を禁止しています。
// Copyright(C) 2011. PLASOL Inc. All Right Reserved.
//--------------------------------------
var geocoder;
var map;
function showGoogleMap(trgt, strLatLng, add, msg)
{
	if (!strLatLng && !add) {
		return false;
	}

	geocoder = new google.maps.Geocoder();
	var myOptions = {
		  mapTypeId: google.maps.MapTypeId.ROADMAP
		, zoom: 15
		, mapTypeControlOptions: { 
			style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
		  }
	}

	map = new google.maps.Map(document.getElementById(trgt), myOptions);
	if (!map) { return false; }

	if (strLatLng != "") {
		strLatLng = strLatLng.replace(/ /g, "");
		strLatLng = strLatLng.split(",");
		var point = new google.maps.LatLng(strLatLng[0], strLatLng[1]);
		map.setCenter(point);
		var marker = new google.maps.Marker({
			map: map,
			position: point,
			draggable: false
		});
		if (msg) {
			var infowindow = new google.maps.InfoWindow({
				content: msg
			});
			google.maps.event.addListener(marker, 'click', function() {
				infowindow.open(map, marker);
			});
			infowindow.open(map, marker);
		}
	} else {
		if (geocoder) {
			geocoder.geocode( { 'address':add, 'region':'ja' }
				, function(results, status) {
					if (status == google.maps.GeocoderStatus.OK) {
						var point = results[0].geometry.location;
						map.setCenter(point);
						var marker = new google.maps.Marker({
							map: map,
							position: point,
							draggable: false
						});
						if (msg) {
							var infowindow = new google.maps.InfoWindow({
								content: msg
							});
							google.maps.event.addListener(marker, 'click', function() {
								infowindow.open(map, marker);
							});
							infowindow.open(map, marker);
						}
					} else {
						//alert("住所の変換に失敗しました。：" + status);
						jQuery("#" + trgt).hide();
					}
				}
			);
		}
	}

	jQuery("#" + trgt).show();
}
