import { ComponentFixture, TestBed } from '@angular/core/testing';

import { EtudiantProfil } from './etudiant-profil';

describe('EtudiantProfil', () => {
  let component: EtudiantProfil;
  let fixture: ComponentFixture<EtudiantProfil>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [EtudiantProfil],
    }).compileComponents();

    fixture = TestBed.createComponent(EtudiantProfil);
    component = fixture.componentInstance;
    await fixture.whenStable();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
