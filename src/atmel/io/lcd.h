#ifndef _LCD_H_
#define _LCD_H_

#include "global.h"
#include <stdio.h>

/* LCD Commands */
#define LCD_CMD_FUNCTION	0x3f	// Function Set: 8-bit, 4-line, 5x8 dots
#define LCD_CMD_DISP		0x0c	// Display On: cursor off, blinking off
#define	LCD_CMD_ENTRY		0x06	// Entry Mode: cursor moves right, shift disabled
#define LCD_CMD_CLEAR		0x01	// Clear Screen

/* LCD Delays */
#define LCD_DELAY_START		30000		// Start-up Delay
#define LCD_DELAY_SHORT     400
#define LCD_DELAY_EN		1

/* LCD Ctrl Pins */
#define LCD_RW(val)        	((val==0) ? (LCD_CTRL_PORT&=~(_BV(LCD_RW_PIN))) : (LCD_CTRL_PORT|=_BV(LCD_RW_PIN))) 
#define LCD_RS(val)       	((val==0) ? (LCD_CTRL_PORT&=~(_BV(LCD_RS_PIN))) : (LCD_CTRL_PORT|=_BV(LCD_RS_PIN))) 
#define LCD_EN(val)      	((val==0) ? (LCD_CTRL_PORT&=~(_BV(LCD_EN_PIN))) : (LCD_CTRL_PORT|=_BV(LCD_EN_PIN))) 

/* LCD Addressing */
#define LCD_DISP_LENGTH		16
#define LCD_ADR_LINE0       0x00
#define LCD_ADR_LINE1       0x40
#define LCD_ADR_LINE2       0x10
#define LCD_ADR_LINE3       0x50

/* LCD Pin Locations */
#define LCD_BUSY_FLAG		8

/* LCD Functions */
void lcdInit();
void lcdClear();
void lcdGoTo(int, int);
void lcdFlashPrint( const char *);


#endif /* _LCD_H_ */
