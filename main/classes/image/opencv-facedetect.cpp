// compile with:
// g++ -O2 -Wall `pkg-config --cflags opencv` `pkg-config --libs opencv` -o opencv-facedetect opencv-facedetect.cpp

// OpenCV
#include "cv.h"
#include "highgui.h"

// C++
#include <iostream>
#include <string>
#include <utility>

// C
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <assert.h>
#include <math.h>
#include <float.h>
#include <limits.h>
#include <time.h>
#include <ctype.h>

using namespace std;

CvSeq* detect( IplImage* img, CvMemStorage* storage, CvHaarClassifierCascade* cascade) {

    cvClearMemStorage(storage);
    if (cascade) {
        return cvHaarDetectObjects(
            img, cascade, storage,
            1.1, 3, CV_HAAR_DO_CANNY_PRUNING,
            cvSize(40, 40)
        );
    }
    return 0;
}

int main( int argc, char** argv ) {

    if ( argc > 1 ) {

        IplImage * image = 0;
        if (strlen(argv[1]) > 0) {

            CvHaarClassifierCascade * cascade = (CvHaarClassifierCascade*) cvLoad("haarcascade_frontalface_alt.xml", 0, 0, 0);
            if ( !cascade ) {
                cout << "{\"status\":\"failed\",\"message\":\"Could not load classifier cascade\"}" <<endl;
                return -1;
            }

            CvMemStorage * storage = cvCreateMemStorage(0);
            image = cvLoadImage(argv[1], CV_LOAD_IMAGE_COLOR);
            if (image == NULL) {
                cout << "{\"status\":\"failed\",\"message\":\"Could not load image: NULL\"}" << endl;
            }
            if (image) {
                CvSeq* faces = detect(image, storage, cascade);
                if (faces->total != 0) {
                	cout << "{\"status\":\"ok\",\"count\":" << faces->total << ",\"faces\":[";

                    for (int i = 0; i < faces->total; i++ ){
				        CvRect* r = (CvRect*)cvGetSeqElem( faces, i );
				        cout << "{\"x\":" << r->x << ",\"y\"" << r->y << ",\"w\":" << r->width << ",\"h\":" << r->height << "}";
				        if (i < faces->total - 1) cout << ",";
				    }

				    cout << "]}" << endl;
                } else {
                    cout << "{\"status\":\"ok\",\"count\":0}" << endl;
                }
                cvReleaseImage(&image);
            } else {
            	cout << "{\"status\":\"failed\",\"message\":\"Could not load the image: like NULL\"}" << endl;
            }
        } else {
            cout << "{\"status\":\"failed\",\"message\":\"Not enough arguments\"}" << endl;
        }
    } else {
        cout << "{\"status\":\"failed\",\"message\":\"Not enough arguments\"}" << endl;
    }
    
    return 0;
}
