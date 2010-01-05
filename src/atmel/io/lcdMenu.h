#ifndef _LCD_MENU_H_
#define _LCD_MENU_H_

#include "global.h"

typedef struct menuItems {
	const char *title;
	struct menu *subMenu;
    pFunc command;	
} menuItems;

typedef struct menu {
	const char *title;
    int numItems;
	menuItems *pMenuItems;
	struct menu *prevMenu;
} menu;

void menuInit( void );
void menuBuild( int8_t );
void menuExecute( void );
void menuBack( void );


#endif /* _LCD_MENU_H_ */
