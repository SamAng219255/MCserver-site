import pynbt
from sys import argv
import gzip
from PIL import Image
import json
from math import floor

palette=[(0,0,0,0),(127, 178, 56,255),(247, 233, 163,255),(199, 199, 199,255),(255, 0, 0,255),(160, 160, 255,255),(167, 167, 167,255),(0, 124, 0,255),(255, 255, 255,255),(164, 168, 184,255),(151, 109, 77,255),(112, 112, 112,255),(64, 64, 255,255),(143, 119, 72,255),(255, 252, 245,255),(216, 127, 51,255),(178, 76, 216,255),(102, 153, 216,255),(229, 229, 51,255),(127, 204, 25,255),(242, 127, 165,255),(76, 76, 76,255),(153, 153, 153,255),(76, 127, 153,255),(127, 63, 178,255),(51, 76, 178,255),(102, 76, 51,255),(102, 127, 51,255),(153, 51, 51,255),(25, 25, 25,255),(250, 238, 77,255),(92, 219, 213,255),(74, 128, 255,255),(0, 217, 58,255),(129, 86, 49,255),(112, 2, 0,255),(209, 177, 161,255),(159, 82, 36,255),(149, 87, 108,255),(112, 108, 138,255),(186, 133, 36,255),(103, 117, 53,255),(160, 77, 78,255),(57, 41, 35,255),(135, 107, 98,255),(87, 92, 92,255),(122, 73, 88,255),(76, 62, 92,255),(76, 50, 35,255),(76, 82, 42,255),(142, 60, 46,255),(37, 22, 16,255)]
shades=[180,220,255,135]

while(len(palette)<64):
	palette.append((255,255,255,0))

exclusions=[390,517,81,455,456,77,64,265,869,1298,759,780,468,153,157,172,398,406,417,422,423,424,795,921,922,1410,2129,2164,1426,1425,1433,920,919,69,2264,19,13,620,2621,5,353,2400,33,878,2829,758]
pictures=[1349]
exclusions.extend(pictures)
allNegatives=[]
allNegativeCoords=[]

def main():
	print(argv)
	path='/home/sam/minecraftServer/world/data/'
	if(len(argv)>3):
		path=argv[3]
	
	regionsDone={}
	tilesUsed={};
	
	unit=int(argv[2])/100
	percent=0;
	
	if modeLrg:
		lowest=[0,0]
		highest=[0,0]
		for i in range(int(argv[2])+1):
			try:
				f=open(path+'map_'+str(i)+'.dat', 'rb')
				nbt=pynbt.NBTFile(gzip.GzipFile(mode='r', fileobj=f))
				f.close()
				if nbt["data"]["scale"].value==0 and i not in exclusions and nbt["data"]["dimension"].value==0:
					x=(nbt["data"]["xCenter"].value//128)
					z=(nbt["data"]["zCenter"].value//128)
					if lowest[0]>x: lowest[0]=x
					if highest[0]<x: highest[0]=x
					if lowest[1]>z: lowest[1]=z
					if highest[1]<z: highest[1]=z
			except:
				if 0==1: print("This shouldn't be printed.")
				#Do Nothing
		tileRange=[highest[0]-lowest[0]+1,highest[1]-lowest[1]+1]
		finImg=Image.new("RGBA",(128*tileRange[0],128*tileRange[1]),(0,0,0,0))
	
	if modeSin:
		fjson=open("tileIds.json","r")
		tilesUsed=json.load(fjson)
		fjson.close()
	
	for i in range(int(argv[2])+1):
		try:
			f=open(path+'map_'+str(i)+'.dat', 'rb')
			nbt=pynbt.NBTFile(gzip.GzipFile(mode='r', fileobj=f))
			f.close()
			if (modeSin and str(nbt["data"]["xCenter"].value//128)+'_'+str(nbt["data"]["zCenter"].value//128)==argv[4]) or not modeSin:
				img=Image.new("RGBA",(128,128),(0,0,0,0))
				imgData=[]
				negatives=[]
				completeness=0
				j=0
				for colId in nbt["data"]["colors"].value:
					if colId<0 and colId not in negatives: negatives.append(colId)
					if colId>4: completeness+=1
					color,shade=divmod(colId,4)
					if color<0 and color not in allNegatives:
						allNegatives.append(color)
						x=nbt["data"]["xCenter"].value+(j%128-64)
						z=nbt["data"]["zCenter"].value+(j//128-64)
						allNegativeCoords.append((color,x,z))
					r,g,b,a=palette[color]
					shaMul=shades[shade]
					imgData.append((((r*shaMul)//255),(g*shaMul)//255,(b*shaMul)//255,a))
					j+=1
				if showNeg and len(negatives)>0: print("Negatives "+json.dumps(negatives)+" found on "+str(i)+" at ("+str(nbt["data"]["dimension"].value//128)+", "+str(nbt["data"]["xCenter"].value//128)+", "+str(nbt["data"]["zCenter"].value//128)+").")
				key=str(nbt["data"]["dimension"].value)+"_"+str(nbt["data"]["xCenter"].value//128)+'_'+str(nbt["data"]["zCenter"].value//128)
				if nbt["data"]["scale"].value!=0: key+="_"+str(nbt["data"]["scale"].value)
				if modeLrg:
					if showDup and nbt["data"]["dimension"].value==0 and key in regionsDone: print("Duplicate of "+key+" found.")
					if showBla and completeness==0: print("Blank Found: "+str(i))
					if i not in exclusions and (((key in regionsDone) and completeness>=regionsDone[key]) or (key not in regionsDone)):
						img.putdata(imgData)
						regionsDone[key]=completeness
						tilesUsed[key]=i
						xOffset=nbt["data"]["xCenter"].value-(lowest[0]*128)
						zOffset=nbt["data"]["zCenter"].value-(lowest[1]*128)
						if not (modeInd or modeOut) and nbt["data"]["dimension"].value==0: finImg.paste(img,box=(xOffset,zOffset))
				else:
					img.putdata(imgData)
					scaleExt=""
					if nbt["data"]["scale"].value!=0: scaleExt+="."+str(nbt["data"]["scale"].value)
					if showDup and nbt["data"]["dimension"].value==0 and key in regionsDone: print("Duplicate of "+key+" found.")
					if showBla and completeness==0: print("Blank Found: "+str(i))
					if i not in exclusions and (((key in regionsDone) and completeness>=regionsDone[key]) or (key not in regionsDone)):
						regionsDone[key]=completeness
						tilesUsed[key]=i
						if not (modeInd or modeOut): img.save('img/tile.'+str(nbt["data"]["dimension"].value)+'.'+str(nbt["data"]["xCenter"].value//128)+'.'+str(nbt["data"]["zCenter"].value//128)+scaleExt+'.png')
					elif i not in exclusions and nbt["data"]["dimension"].value!=0:
						if not (modeInd or modeOut): img.save('img/tile.'+str(nbt["data"]["dimension"].value)+'.'+str(nbt["data"]["xCenter"].value//128)+'.'+str(nbt["data"]["zCenter"].value//128)+scaleExt+'.png')
					if i in exclusions:
						if not (modeInd or modeOut): img.save('img/excluded/tile_'+str(i)+'.'+str(nbt["data"]["dimension"].value)+'.'+str(nbt["data"]["xCenter"].value//128)+'.'+str(nbt["data"]["zCenter"].value//128)+scaleExt+'.png')
		except FileNotFoundError:
			if showErr: print('File Not Found on '+str(i))
		if(floor(i%unit)==0):
			if showPer: print(argv[1]+":	"+str(percent)+"% Complete.")
			percent+=1
	negF=open("negativeTiles.txt","w")
	for color,x,z in allNegativeCoords:
		negF.write(str(color)+"	ex:	"+str(x)+",	"+str(z)+"\n")
	negF.close()
	if modeLrg: finImg.save('img/full.png')
	if showTot and modeLrg: print(json.dumps(tileRange))
	if showTot: print(str(len(regionsDone)))
	if not modeOut:
		tileF=open("tileIds.json","w")
		json.dump(tilesUsed,tileF);
		tileF.close();

if __name__=='__main__':
	showDup="d" in argv[1]
	showErr="e" in argv[1]
	showNeg="n" in argv[1]
	showPer="p" in argv[1]
	showTot="t" in argv[1]
	showBla="b" in argv[1]
	modeInd="i" in argv[1]
	modeOut="o" in argv[1]
	modeSin="s" in argv[1]
	modeLrg="l" in argv[1]
	modeHlp="h" in argv[1]
	if not modeHlp:
		main()
	else:
		print("Syntax:",
			"python3 genImg.py [arguments] [numberOfMapFiles] [path]",
			"python3 genImg.py [arguments] [numberOfMapFiles] [path] [tileKey]",
			"",
			"Arguments:",
			"b - Show Blank Map Files",
			"d - Show Duplicates",
			"e - Show Missing Files",
			"h - Show This Dialogue",
			"i - Only Create Index",
			"l - Generate Single Map Image Instead Of Multiple Tiles. Including t As Well Will Cause It To Output Dimension Of Image In Tiles",
			"n - Show Maps With Negative Palette Indexes",
			"o - Output Only, Doesn't Save Anything. Does Nothing Without b, d, e, p, or t Selected",
			"p - Show Percentage Complete",
			"s - Only Update A Specified Tile Which Is Specified Using The 'tileKey' Parameter",
			"t - Show Total Tiles Generated After Completion",
			"",
			"Description:",
			"Generates images (\"tiles\") for each mappable region from map_n.dat files found at [path]. It will read all such files up to map_[numberOfMapFiles].dat.",
			sep="\n")
