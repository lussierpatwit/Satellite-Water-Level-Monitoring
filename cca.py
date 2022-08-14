import numpy as np
import pandas
import pandas as pd
import matplotlib.pyplot as plt
from skimage.io import imread, imshow
from skimage.color import rgb2gray
from skimage.morphology import (erosion, dilation, closing, opening,
                                area_closing, area_opening)
from skimage.measure import label, regionprops, regionprops_table
from pdf2image import convert_from_path
from IPython.display import display
from random import randint
import matplotlib
import os
import cv2 as cv
import geojson as gj
import uuid
import glob

#Ian Maloney
#


#Our data input directory
#***CHANGE IF DIRECTORY IS MOVED***
input_directory = r"D:\FinalProcess\In"

#Our output directory for the CSV data
#***CHANGE IF DIRECTORY IS MOVED***
csv_output_directory = r"D:\FinalProcess\Out\CSV"

#Our output directory for the imagery data
#***CHANGE IF DIRECTORY IS MOVED***
image_output_directory = r"D:\FinalProcess\Out\Images"

#createCSV converts the pandas dataframe into a CSV file, which is used
#as part of the input for the database

#Passed - df : pandas dataframe consisting of the id number,
#              lake name, date and surface area of each entry
def createCSV(df):

    os.chdir(csv_output_directory)

    open('lake_data.csv', 'w+')
    df.to_csv('lake_data.csv', header= None, index = False)

    os.chdir(input_directory)


#saveImage saves the inputed image as a jpeg, which is used as
#as part of the input for the database

#Passed - image: masked image of the body of water, to be saved as jpeg
#         id: the unique id number for said image, used as primary key in our database
def saveImage(image,id):
    os.chdir(image_output_directory)
    plt.imsave(id + ".jpeg", image, cmap = "gray")
    os.chdir(input_directory)


#calculateSAandLakeName calculates the the surface area of the input image.
#with the calculated surface area the proper lake is determined

#Passed - image :  masked image of the body of water, in binary

#Variables - w : total width of the image
#            h : total height of the image
#            count : total pixels of lake in image
#            area : calulated area. formula due to each image being 100km x 100km , we can
#                   multiply the count by this total distance, then divide by the width and
#                   height of the image
def calculateSAandLakeName(image):
    w = image.shape[1]
    h = image.shape[0]
    count = np.sum(image)
    area = count*100*100 /(w*h)

    if area < 160 :
            lake_name = 'Mono'
    else:
            lake_name = 'Mead'

    return area , lake_name


#findInputFile traverses the input files to find the input image within in pdf format

#Passed: - i : index of what input file to enter first in directory
#          cur_dir : current directory
#          dir_list : list of directories within the current directory

#Return: - os.listdir()[4] - the pdf of the lake image, which is always the 5th entry
#                            in the directory
def findInputFile(i, cur_dir, dir_list):
    try:
        os.chdir(cur_dir + "\\" + str(dir_list[i]))
        os.chdir(os.getcwd() + "\\" + str(os.listdir()[0]))
        pdf_path = str(os.getcwd() + "\\" + os.listdir()[3])
        pdfToJpeg(pdf_path)
        return os.listdir()[4]
    except:
        print("Not Directory, skipping")
        return None

#parseDate parses the string date in order to add in hiphens for our database for a cleaner look

#Passed: - in_date: the input string date

#Return: the parsed date with hiphens inserted
def parseDate(in_date):
    year = in_date[0:4]
    month = in_date[4:6]
    day = in_date[6:8]
    return ( year + "-" + month + "-" + day )



#pdftoJpeg converts the input pdf image into a jpeg which then can be processed in our findComponents algorithm

#Passed - pdf: the pdf image
def pdfToJpeg(pdf):
    pages = convert_from_path(pdf, 200, poppler_path=r'C:\Users\maloneyi\Downloads\poppler-22.04.0\Library\bin')
    jpeg = pages[0].save('out.jpg', 'JPEG')



#findDate finds the current index's date from the directory name.

#Passed - file_name: the name of the input file, where the date is within

#Calls : parseDate: in order to reformat the date for better readability

#Returns : out_name: output from parseDate method
def findDate(file_name):
    out_name = file_name[11:19]
    out_name = parseDate(out_name)
    return out_name


# findComponents utilizes connected component analysis to remove noise from the inputed images , in order to isolate
# the desired body of water. Removal is done by the component properties, the properties used to isolate the body of
# water are the total component area, and the solidity of the detected component

# Passed - image: the original input image with labels where the body of water is found inside, used to get the component properties
#          image_gray: gray scale version of the image used to pull the correct components

# Output - the output mask of the isolated body of water, used for our final image output into our database
def findComponents(image,image_gray):

    regions = regionprops(image)
    properties = ['area', 'convex_area', 'bbox_area', 'extent', 'mean_intensity', 'solidity', 'eccentricity',
                  'orientation']
    df = pd.DataFrame(regionprops_table(image, image_gray,
                                        properties=properties))
    mask = []
    bbox = []
    list_of_index = []

    for num, x in enumerate(regions):
        area = x.area
        eccentricity = x.eccentricity
        solidity = x.solidity

        if (num != 0 and (9900 < area < 190000) and .17 < solidity <.858 ):
            print("save " + str(num))
            mask.append(regions[num].convex_image)
            print(print(x.solidity))
            bbox.append(regions[num].bbox)
            list_of_index.append(num)

    out = np.zeros_like(image)

    for x in list_of_index:
        out += (image == x + 1).astype(int)

    return out

#Our main function carries out looping through the main input directory , for every iteration finds the image path in
#order to pull it, gray scales the image, binarizes it in order to be labelled findComponents is called in order to
#utilize CCA to isolate the body of  water. Other output data points such as lake name, surface area and date are created
#and called here, then put into our temp data frame , which is then appended to our output dataframe. We then save the
#image with the unique id as the name. Once every iteration is complete, we then write the dataframe into the proper
#directory as a csv.

#Variables -
#              output_df: the data frame will all outputs will be saved from every iteration
#              input_directory_list: list of every file/directory in our input directory
#              i: used to iterate over the input file, will end as total length of the input file.
#              im_path: path to the current iterations image
#              id: the unique identifier for each iteration, which is the string cast of i
#              im: the image pulled from im_path
#              im_gray: grayscaled version of im using skimage's rgb2gray function. only takes in im as parameter
#              im_binared: binarized version of the im_gray image, down to <0.1 intensity to get rid of as much noise as posible
#              im_labelled: labelled version of binarized images, gives every part of the image a specific component,
#                           used in order to disect properties of every component in image in our findComponents class
#              im_mask: our final output mask, utilizing CCA in our findComponents class call where we pass im_lablled and im_gray
#              date: the date of the current image , saved in our output.
#              surface_area: the calculated surface area of the lake isolated in our image
#              lake_name: the name of the lake isolated in our image
#              temp_df: Temporary data frame to store id, date , surface_area and lake_name of the current iteration
#              output_df: our output dataframe , where every iteration is stored in.


#Non Variable Class Calls -
#               saveImage(im_mask, id): saves im_mask as a jpeg into our output directory, with the unique
#                                       id as the file name, as id is our primary key within our database. Ran during
#                                       every iteration as every im* variable gets recycled during every iteration
#               createCSV(output_df):   saves output_df as a csv file in our ouput directory, where every iteration is
#                                       saved within. Every iteration has the values of id, date, surface_area and lake_name
#                                       for said iteration. Ran at end of script.
output_df = pandas.DataFrame()
os.chdir(input_directory)
input_directory_list = os.listdir()
i=0
for i in range(0, len(input_directory_list)):
    print('Starting image % s' % i)
    im_path = findInputFile(i, input_directory, input_directory_list)

    id = str(i)

    im = imread(im_path)
    im_gray = rgb2gray(im)
    im_binarized = im_gray<0.1
    im_labelled = label(im_binarized)
    im_mask = findComponents(im_labelled,im_gray)

    date = findDate(str(input_directory_list[i]))

    surface_area, lake_name = calculateSAandLakeName(im_mask)

    data = [[id, lake_name, date, str(surface_area)]]
    temp_df = pd.DataFrame(data)
    output_df = output_df.append(temp_df, ignore_index= True)

    saveImage(im_mask,id)

    print('Image % s completed' % i)

createCSV(output_df)
