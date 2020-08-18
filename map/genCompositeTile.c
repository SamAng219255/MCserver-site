#include "png.h"

unsigned char int2Byte(int x) {
	return (unsigned char)(x<0?0:(x>255?255:x));
}

void buildRegionTile(int x, int y, int scale, int resolution, png_bytep result) {
	
}

int main(int argc, char* argv[]) {
	if(argc<4) {
		printf("Usage:\n%s x y scale resolution\nArguments:\n",argv[0]);
		printf("\tx, y\n\t\tThe north-western map of for the composite tile.\n");
		printf("\tscale\n\t\tNumber of maps across and down to build the composite out of.\n");
		printf("\tresolution\n\t\tNumber of pixels across and down to generate.\n");
		return 0;
	}
	int x=intFromStr(argv[1]);
	int y=intFromStr(argv[2]);
	int scale=intFromStr(argv[3]);
	int resolution=128;
	if(argc>4) {
		resolution=intFromStr(argv[4]);
	}
	png_bytep buffer=(png_bytep)malloc(scale*scale*3);
	buildRegionTile(x,y,scale,resolution,buffer);

	FILE *fp;
	fp = fopen(argv[8],"wb");
	if(fp==NULL) {
		printf("no file... name should be %s\n",argv[8]);
		free(buffer);
		return 1;
	}

	//Allocating and initialzing the png_struct and png_info variables
	png_structp png_ptr = png_create_write_struct(PNG_LIBPNG_VER_STRING, NULL, NULL, NULL);
	if (!png_ptr)
		return 1;
	png_infop info_ptr = png_create_info_struct(png_ptr);
	if (!info_ptr) {
		png_destroy_write_struct(&png_ptr, (png_infopp)NULL);
		return 1;
	}

	//libpng expects to longjmp() back to this code if it encounters an error
	if (setjmp(png_jmpbuf(png_ptr))) {
		png_destroy_write_struct(&png_ptr, &info_ptr);
		fclose(fp);
		free(buffer);
		return 1;
	}

	//set file to write to
	png_init_io(png_ptr, fp);

	//write IHDR
	//png_set_IHDR(png_ptr, info_ptr, width, height, bit_depth, color_type, interlace_type, compression_type, filter_method)
	png_set_IHDR(png_ptr, info_ptr, dx, dy, 8, PNG_COLOR_TYPE_RGB, PNG_INTERLACE_ADAM7, PNG_COMPRESSION_TYPE_DEFAULT, PNG_FILTER_TYPE_DEFAULT);

	//actually write the data
	png_write_info(png_ptr, info_ptr);

	//format the image data structure
	png_byte *row_pointers[dy];
	for (int i=0; i<dy; i++)
		row_pointers[i]=buffer+i*dx*3;

	//write image data
	png_write_image(png_ptr, row_pointers);

	//write end
	png_write_end(png_ptr, info_ptr);

	//free memory
	png_destroy_write_struct(&png_ptr, &info_ptr);

	fclose(fp);
	free(buffer);
	return 0;
}