import java.io.*;
import java.net.Socket;

public class SerialPHPWriter implements Runnable{

	private OutputStream out;
	private BufferedReader in;
	private String readLine;
	
	public SerialPHPWriter( OutputStream out ){
		this.out = out;
		
		try{
	        Socket mySocket = new Socket( "127.0.0.1", 6000 );
	        InputStreamReader myReader = new InputStreamReader(new BufferedInputStream(mySocket.getInputStream()));
	        in = new BufferedReader(myReader);
    	} catch( IOException ex ){ ex.printStackTrace();}
    	
	}
	
	public void run(){
		while(true){
			try{
				while( (readLine = in.readLine()) != null ){
					System.out.print("Send via PHP: ");
					
					byte[] outArray = readLine.getBytes();

					for ( int i = 0; i < outArray.length; i++ )
		            {
		                this.out.write(outArray[i]);
		                System.out.print( String.format("%x ", outArray[i]));
		            }

					System.out.println();
				}
			} catch( IOException ex ){ ex.printStackTrace(); }
		}
	}
}
