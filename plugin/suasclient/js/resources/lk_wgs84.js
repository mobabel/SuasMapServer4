/* JavaScript zum Umrechnen von Schweizer Landeskoordinaten CH-1903
   in geographische Koordinaten im WGS84-System

   Die Umrechnung basiert auf folgendem Material des Bundesamtes fuer
   Landestopographie der Schweiz, Wabern:
     1. Schweizerisches Projektionssystem. Formeln fuer die 
        Umrechnung von Landeskoordinaten in geographische 
	Koordinaten und umgekehrt, 1984
     2. Transformation von Landeskoordinaten CH-1903 in WGS-84 
        Koordinaten, 1990
     3. Ergaenzung zur Formelzusammenstellung fuer die Umrechnung
        von WGS84-Koordinaten in Schweizerische Projektionskoordinaten
   
   (c) 	AMRON 2001
	Norbert.Marwan@gmx.net, Potsdam   */

function Trafo(flag)
 {
   skale=180/Math.PI;
   if(flag==1) WGS2CH();
   else CH2WGS();
   return;
 }

function WGS2CH()
 {
   B=document.forms['WGS'].B.value;
   L=document.forms['WGS'].L.value;
   H=document.forms['WGS'].H.value;

   lambda=gradminsec2grad(L)/skale;
   phi=gradminsec2grad(B)/skale;
   h=H*1.0;
   
	a=6378137.000;
	e=0.00669438000;

	Rn=a/Math.sqrt(1-e*Math.pow(Math.sin(phi),2));

	xWGS=(Rn+h)*Math.cos(phi)*Math.cos(lambda);
	yWGS=(Rn+h)*Math.cos(phi)*Math.sin(lambda);
	zWGS=(Rn*(1-e)+h)*Math.sin(phi);

	dX=-660.075;
	dY=-13.551;
	dZ=-369.34;
	M=0.99999436;
	alpha=(-2.485/10000)*Math.PI/200;
	beta=(-1.783/10000)*Math.PI/200;
	gamma=(-2.939/10000)*Math.PI/200;

	xCH=dX+(M*(Math.cos(beta)*Math.cos(gamma)*xWGS+(Math.cos(alpha)*Math.sin(gamma)+(Math.sin(alpha)*Math.sin(beta)*Math.cos(gamma)))*yWGS + (Math.sin(alpha)*Math.sin(gamma)-(Math.cos(alpha)*Math.sin(beta)*Math.cos(gamma)))*zWGS));
	yCH=dY+(M*(-Math.cos(beta)*Math.sin(gamma)*xWGS+(Math.cos(alpha)*Math.cos(gamma)-(Math.sin(alpha)*Math.sin(beta)*Math.sin(gamma)))*yWGS + (Math.sin(alpha)*Math.cos(gamma)-(Math.cos(alpha)*Math.sin(beta)*Math.sin(gamma)))*zWGS));
	zCH=dZ+(M*(Math.sin(beta)*xWGS-(Math.sin(alpha)*Math.cos(beta)*yWGS) + (Math.cos(alpha)*Math.cos(beta))*zWGS));

	a=6377397.155;
	e=0.006674372231;

	lCH=Math.atan(yCH/xCH);
	
	phiCH=46.952405555*Math.PI/180;
	er=1;
	while(er>0.00000000000000000000000001)
	{
	er=phiCH;
	Rn=a/Math.sqrt(1-e*Math.pow(Math.sin(phiCH),2));
	hCH=Math.sqrt(Math.pow(xCH,2)+Math.pow(yCH,2))/Math.cos(phiCH)-Rn;
	phiCH=Math.atan((zCH/Math.sqrt(Math.pow(xCH,2)+Math.pow(yCH,2)))/(1-Rn*e/(Rn+hCH)));
	er=Math.abs(er-phiCH);
	}


	B0=0.81947406867611;
	L0=0.1298452241431;
	b0=0.81869435858167;
	e=Math.sqrt(0.006674372230614);
	a=6377397.155;
	K=0.0030667323772751;
	alpha=1.00072913843038;
	R=6378815.90365;
	
	S=(alpha*Math.log(Math.tan((Math.PI/4)+(phiCH/2)))) - ((alpha*e/2)*Math.log( (1+(e*Math.sin(phiCH))) / (1-(e*Math.sin(phiCH))) ))+K;

	phiCH=2*(Math.atan(Math.exp(S))-Math.PI/4);
	lCH=alpha*(lCH-L0);

	lambda=Math.atan(Math.sin(lCH)/((Math.sin(b0)*Math.tan(phiCH))+(Math.cos(b0)*Math.cos(lCH))));
	phi=Math.asin((Math.cos(b0)*Math.sin(phiCH))-(Math.sin(b0)*Math.cos(phiCH)*Math.cos(lCH)));

	Y=600000+R*lambda;
	X=200000+(R/2)*Math.log((1+Math.sin(phi))/(1-Math.sin(phi)));
	
	Y=Math.round(Y*10)/10;
	X=Math.round(X*10)/10;
	H=Math.round(hCH*10)/10;

	document.forms['CH'].H.value=H;
	document.forms['CH'].B.value=X;
	document.forms['CH'].L.value=Y;
 }

function CH2WGS()
 {
   X=document.forms['CH'].B.value;
   Y=document.forms['CH'].L.value;
   Z=document.forms['CH'].H.value;



	B0=0.81947406867611;
	L0=0.1298452241431;
	b0=0.81869435858167;
	e=Math.sqrt(0.006674372230614);
	a=6377397.155;
	K=0.0030667323772751;
	alpha=1.00072913843038;
	R=6378815.90365;
	
	phi=2*(Math.atan(Math.exp((X-200000)/R))-Math.PI/4);
	lambda=(Y-600000)/R;


	lCH=Math.atan(Math.sin(lambda)/(-Math.sin(b0)*Math.tan(phi)+Math.cos(b0)*Math.cos(lambda)));
	phiCH=Math.asin(Math.cos(b0)*Math.sin(phi)+Math.sin(b0)*Math.cos(phi)*Math.cos(lambda));


	phi=B0;
	er=1;
	while(er>0.00000000000000000000000001)
	{
	er=phi;
	S=(1/alpha)*(Math.log(Math.tan(Math.PI/4+phiCH/2))-K)+((e/2)*Math.log( (1+(e*Math.sin(phi))) / (1-(e*Math.sin(phi))) ));
	phi=2*(Math.atan(Math.exp(S))-Math.PI/4);
	er=Math.abs(er-phi);
	}

	l=lCH/alpha+L0;
	h=Z*1.0;

	e=0.006674372230614;
	a=6377397.155;

	Rn=a/Math.sqrt(1-e*Math.pow(Math.sin(phi),2));

	xCH=(Rn+h)*Math.cos(phi)*Math.cos(l);
	yCH=(Rn+h)*Math.cos(phi)*Math.sin(l);
	zCH=(Rn*(1-e)+h)*Math.sin(phi);

	dX=660.075;
	dY=13.551;
	dZ=369.34;
	M=1.00000566;
	alpha=(2.485/10000)*Math.PI/200;
	beta=(1.783/10000)*Math.PI/200;
	gamma=(2.939/10000)*Math.PI/200;


	xWGS=dX+(M*(Math.cos(beta)*Math.cos(gamma)*xCH+(Math.cos(alpha)*Math.sin(gamma)+(Math.sin(alpha)*Math.sin(beta)*Math.cos(gamma)))*yCH + (Math.sin(alpha)*Math.sin(gamma)-(Math.cos(alpha)*Math.sin(beta)*Math.cos(gamma)))*zCH));
	yWGS=dY+(M*(-Math.cos(beta)*Math.sin(gamma)*xCH+(Math.cos(alpha)*Math.cos(gamma)-(Math.sin(alpha)*Math.sin(beta)*Math.sin(gamma)))*yCH + (Math.sin(alpha)*Math.cos(gamma)-(Math.cos(alpha)*Math.sin(beta)*Math.sin(gamma)))*zCH));
	zWGS=dZ+(M*(Math.sin(beta)*xCH-(Math.sin(alpha)*Math.cos(beta)*yCH) + (Math.cos(alpha)*Math.cos(beta))*zCH));

	a=6378137
	e=0.00669438

	lambda=Math.atan(yWGS/xWGS);
	
	phi=46.952405555*Math.PI/180
	er=1
	while(er>0.00000000000000000000000001)
	{
	er=phi
	Rn=a/Math.sqrt(1-e*Math.pow(Math.sin(phi),2))
	h=Math.sqrt(Math.pow(xWGS,2)+Math.pow(yWGS,2))/Math.cos(phi)-Rn
	phi=Math.atan((zWGS/Math.sqrt(Math.pow(xWGS,2)+Math.pow(yWGS,2)))/(1-Rn*e/(Rn+h)))
	er=Math.abs(er-phi)
	}

	h=Math.round(h*10)/10;

	document.forms['WGS'].H.value=h;
	document.forms['WGS'].B.value=grad2gradminsec(phi*180/Math.PI);
	document.forms['WGS'].L.value=grad2gradminsec(lambda*180/Math.PI);
 }
 
function grad2gradminsec(X)
 {
   grad=Math.floor(X);
   min=60*(X-grad);
   min2=Math.floor(min);
   sec=60*(min-min2);
   Y=grad + ":" + min2 + ":" + Math.round(sec*100)/100; 
   return Y;
 }

function gradminsec2grad(X)
 {
   werte=X.split(":");
   sec=werte[2]/60;
   min=(1*werte[1]+sec)/60;
   Y=1*werte[0]+min;
   return Y;
 }
