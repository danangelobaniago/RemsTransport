const vans = {

hiace:{
name:"Toyota Hiace Commuter",
price:"₱3,500 / Day",
image:"toyotacommutervan.png",
passengers:"15 Passengers",
transmission:"Manual",
fuel:"Diesel",
description:"The Toyota Hiace is one of the most reliable vans for group transportation. Perfect for airport transfers, tours, and company travel."
},

tourer:{
name:"Toyota Hiace Tourer",
price:"₱4,200 / Day",
image:"toyotacommuterdeluxe.png",
passengers:"14 Passengers",
transmission:"Automatic",
fuel:"Diesel",
description:"A premium touring van designed for long-distance travel with better comfort and seating."
},

deluxe:{
name:"Toyota Hiace Deluxe",
price:"₱4,500 / Day",
image:"toyotacommutervan.png",
passengers:"12 Passengers",
transmission:"Automatic",
fuel:"Diesel",
description:"A luxury van with premium interior suitable for VIP trips and corporate transport."
},

nv350:{
name:"Nissan NV350",
price:"₱3,800 / Day",
image:"nissanurvan.png",
passengers:"15 Passengers",
transmission:"Manual",
fuel:"Diesel",
description:"A spacious van known for reliability and comfort for family trips and tours."
}

};

// use vanID from Blade
const van = vans[vanID];

if (van) {

document.getElementById("vanTitle").innerText = van.name;
document.getElementById("vanPrice").innerText = van.price;
document.getElementById("vanImage").src = "/images/" + van.image;
document.getElementById("vanPassengers").innerText = van.passengers;
document.getElementById("vanTransmission").innerText = van.transmission;
document.getElementById("vanFuel").innerText = van.fuel;
document.getElementById("vanDescription").innerText = van.description;

document.getElementById("bookBtn").href = "/booking?van=" + vanID;

} else {

// fallback if URL is wrong
document.querySelector(".container").innerHTML =
"<h2 style='text-align:center'>Van not found</h2>";

}
