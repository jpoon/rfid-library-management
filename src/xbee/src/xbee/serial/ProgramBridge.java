import java.io.*;
import java.net.*;
import java.util.*;

public class ProgramBridge {
	
	ArrayList<PrintWriter> clientOutputStreams;
	PrintWriter PHPout;
	OutputStream transferOutputStream;
	InputStream transferInputStream;

	public static void main(String[] args) {
		ProgramBridge myBridge = new ProgramBridge();
		myBridge.go();
	}
	
	public class ClientHandler implements Runnable{
		
		BufferedReader in;
		PrintWriter out;
		Socket clientSocket;
		
		public ClientHandler(Socket socket, PrintWriter out){
			try{
				clientSocket = socket;
				in = new BufferedReader( new InputStreamReader(clientSocket.getInputStream()) );
				this.out = out;
			} catch( Exception ex ){ex.printStackTrace();}
		}
		
		public void run(){
			String dataOne;
			try{
				while( (dataOne = in.readLine()) != null ){
					if( dataOne.equals("GET_RFID") ){
						int index = clientOutputStreams.indexOf(out);
						clientOutputStreams.remove(index);
						PHPout = out;
					}
					else if( (byte) dataOne.charAt(2) == 0x21 ){
						sendToPHP( dataOne );
					}
					else{
						sendData( dataOne );
					}
				}
				
			} catch( Exception ex ){ ex.printStackTrace(); }
		}
		
	}
	
	public void go(){
		clientOutputStreams = new ArrayList<PrintWriter>();
		
		try{
			ServerSocket serverSock = new ServerSocket(6000);
			System.out.println("Bridge Started");
			
			while(true){
				Socket clientSocket = serverSock.accept();
				PrintWriter out = new PrintWriter(clientSocket.getOutputStream());
				clientOutputStreams.add(out);
				
				Thread clientThread = new Thread( new ClientHandler(clientSocket, out) );
				clientThread.start();
				
				System.out.println("Client Added");
				
			}
		} catch( IOException ex ){ ex.printStackTrace(); }
		
	}
	
	public void sendData( String dataOne ){
		Iterator<PrintWriter> it = clientOutputStreams.iterator();
		while( it.hasNext() ){
			PrintWriter out = it.next();
			out.println(dataOne);
			out.flush();
		}
	}
	
	public void sendToPHP( String dataOne ){
		PHPout.println(dataOne);
		PHPout.flush();
	}

}
